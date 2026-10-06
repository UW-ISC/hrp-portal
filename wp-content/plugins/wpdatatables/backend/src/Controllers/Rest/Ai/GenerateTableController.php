<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Controllers\Rest\Ai;

use WPDataTables\Controllers\Controller;
use WPDataTables\Common\Exceptions\InvalidArgumentException;
use WPDataTables\Services\Ai\AiService;
use WPDataTables\Services\Ai\PromptBuilder;
use WPDataTables\Services\Ai\OutputValidator;
use WPDataTables\Services\Ai\SchemaContextService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * POST /wpdatatables/v1/ai/generate-table — AI Table Generator
 *
 * Turns a plain-English description into a column scaffold (manual) or SQL-linked
 * table proposal (sql). The AI never writes to the database — the client reviews
 * and applies the result through the existing Create Table wizard.
 *
 * @package WPDataTables\Controllers\Rest\Ai
 */
class GenerateTableController extends Controller
{
    /** Output-token cap for this endpoint */
    const MAX_TOKENS = 800;

    /** @var AiService */
    private $ai;

    /** @var PromptBuilder */
    private $prompts;

    /** @var OutputValidator */
    private $validator;

    /** @var SchemaContextService */
    private $schema;

    public function __construct(
        AiService $ai,
        PromptBuilder $prompts,
        OutputValidator $validator,
        SchemaContextService $schema
    ) {
        $this->ai        = $ai;
        $this->prompts   = $prompts;
        $this->validator = $validator;
        $this->schema    = $schema;
    }

    /**
     * @param WP_REST_Request $data
     * @return WP_REST_Response
     * @throws InvalidArgumentException When the description is empty.
     */
    protected function handle(WP_REST_Request $data): WP_REST_Response
    {
        $description = trim((string) $data->get_param('description'));
        if ($description === '') {
            throw new InvalidArgumentException('A table description is required.');
        }

        $tableType  = $this->resolveTableType($data);
        $connection = sanitize_text_field((string) $data->get_param('connection'));
        $model      = (string) $data->get_param('model');

        $schemaContext = $tableType === 'sql'
            ? $this->schema->getContext($connection)
            : [];

        $prompt = $this->prompts->tableGeneratorPrompt($tableType, $description, $schemaContext);

        $parsed = $this->ai->generateJson($prompt['system'], $prompt['user'], self::MAX_TOKENS, $model);

        $columns = [];
        foreach ((array) ($parsed['columns'] ?? []) as $column) {
            if (is_array($column)) {
                $columns[] = $this->validator->normalizeColumnType($column);
            }
        }

        $warnings = [];
        foreach ((array) ($parsed['warnings'] ?? []) as $warning) {
            if (is_string($warning) && $warning !== '') {
                $warnings[] = sanitize_text_field($warning);
            }
        }

        $response = [
            'title'         => isset($parsed['title']) ? sanitize_text_field((string) $parsed['title']) : '',
            'columns'       => $columns,
            'table_type'    => $tableType,
            'warnings'      => $warnings,
            'sql'           => '',
            'suggested_sql' => '',
        ];

        if ($tableType === 'sql') {
            $sql = $this->validator->sanitizeSql((string) ($parsed['sql'] ?? $parsed['suggested_sql'] ?? ''));
            if ($sql === '' && isset($parsed['sql']) && trim((string) $parsed['sql']) !== '') {
                $warnings[] = 'The AI returned SQL that was rejected as unsafe (only single SELECT / WITH queries are allowed).';
            }
            $response['sql']           = $sql;
            $response['suggested_sql'] = $sql;
            $response['warnings']      = $warnings;
        }

        return new WP_REST_Response($response, 200);
    }

    /**
     * Resolve table_type from the request, with one-release legacy mapping.
     *
     * @param WP_REST_Request $data
     * @return string One of manual|sql.
     */
    private function resolveTableType(WP_REST_Request $data): string
    {
        $tableType = sanitize_key((string) $data->get_param('table_type'));
        if (in_array($tableType, ['manual', 'sql'], true)) {
            return $tableType;
        }

        // Legacy contract: data_source=mysql → sql, everything else → manual.
        $dataSource = (string) $data->get_param('data_source');

        return $dataSource === 'mysql' ? 'sql' : 'manual';
    }
}
