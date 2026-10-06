<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers\Rest\Ai;

use WPDataTables\Controllers\Controller;
use WPDataTables\Common\Exceptions\InvalidArgumentException;
use WPDataTables\Common\Exceptions\NotFoundException;
use WPDataTables\Services\Ai\AiService;
use WPDataTables\Services\Ai\PromptBuilder;
use WPDataTables\Services\Ai\OutputValidator;
use WPDataTables\Services\Ai\TableMetaService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * POST /wpdatatables/v1/ai/suggest-chart — Chart Type Suggester.
 *
 * Inspects a table's column schema plus a small data sample and recommends a
 * render engine, chart type, and axis configuration. Never writes to the
 * database — the client reviews and applies through the chart wizard.
 *
 * @package WPDataTables\Controllers\Rest\Ai
 */
class SuggestChartController extends Controller
{
    /** Output-token cap for this endpoint. */
    const MAX_TOKENS = 700;

    /** @var AiService */
    private $ai;

    /** @var PromptBuilder */
    private $prompts;

    /** @var OutputValidator */
    private $validator;

    /** @var TableMetaService */
    private $tableMeta;

    public function __construct(
        AiService $ai,
        PromptBuilder $prompts,
        OutputValidator $validator,
        TableMetaService $tableMeta
    ) {
        $this->ai        = $ai;
        $this->prompts   = $prompts;
        $this->validator = $validator;
        $this->tableMeta = $tableMeta;
    }

    /**
     * @param WP_REST_Request $data
     * @return WP_REST_Response
     * @throws InvalidArgumentException|NotFoundException
     */
    protected function handle(WP_REST_Request $data): WP_REST_Response
    {
        $tableId = (int) $data->get_param('table_id');
        if ($tableId <= 0) {
            throw new InvalidArgumentException('A valid table_id is required.');
        }

        $meta = $this->tableMeta->getTableMeta($tableId);
        if ($meta === null) {
            throw new NotFoundException('Table not found or has no columns.');
        }

        if (($meta['type'] ?? '') === 'simple') {
            throw new InvalidArgumentException('Simple tables cannot be used as chart data sources.');
        }

        $description = trim((string) $data->get_param('description'));
        $model       = (string) $data->get_param('model');

        if (strlen($description) > 2000) {
            $description = substr($description, 0, 2000);
        }

        $availableEngines = $this->parseAvailableEngines($data->get_param('available_engines'));

        $sample = $this->tableMeta->getSampleData($tableId);
        $prompt = $this->prompts->chartSuggesterPrompt($meta, $sample, $description, $availableEngines);
        $parsed = $this->ai->generateJson($prompt['system'], $prompt['user'], self::MAX_TOKENS, $model);

        $allowedColumns = [];
        foreach ((array) ($meta['columns'] ?? []) as $column) {
            if (is_array($column) && !empty($column['name'])) {
                $allowedColumns[] = (string) $column['name'];
            }
        }

        $suggestion = $this->validator->normalizeChartSuggestion(
            $parsed,
            $allowedColumns,
            $availableEngines,
            (array) ($meta['columns'] ?? []),
            $description
        );
        $suggestion['table_id'] = $tableId;
        $suggestion['columns']  = $meta['columns'];

        return new WP_REST_Response($suggestion, 200);
    }

    /**
     * @param mixed $raw
     * @return array<int, string>
     */
    private function parseAvailableEngines($raw): array
    {
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $raw = $decoded;
            } else {
                $raw = array_map('trim', explode(',', $raw));
            }
        }

        if (!is_array($raw)) {
            return OutputValidator::ALLOWED_CHART_ENGINES;
        }

        $engines = [];
        foreach ($raw as $engine) {
            $engine = sanitize_key((string) $engine);
            if (in_array($engine, OutputValidator::ALLOWED_CHART_ENGINES, true)) {
                $engines[] = $engine;
            }
        }

        return $engines !== [] ? array_values(array_unique($engines)) : OutputValidator::ALLOWED_CHART_ENGINES;
    }
}
