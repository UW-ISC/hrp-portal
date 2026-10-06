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
 * POST /wpdatatables/v1/ai/query-assistant — SQL Query Assistant.
 *
 * Helps admins generate, fix, improve, or explain SQL for query-based
 * wpDataTables. Returns proposed SQL + explanation; never writes to the DB.
 *
 * @package WPDataTables\Controllers\Rest\Ai
 */
class QueryAssistantController extends Controller
{
    /** Output-token cap for SQL generation. */
    const MAX_TOKENS = 1200;

    /** @var array<int, string> */
    const ACTIONS = ['generate', 'fix', 'improve', 'explain'];

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
     * @throws InvalidArgumentException
     */
    protected function handle(WP_REST_Request $data): WP_REST_Response
    {
        $mode = sanitize_key((string) $data->get_param('mode'));
        if (!in_array($mode, self::ACTIONS, true)) {
            throw new InvalidArgumentException('Invalid query assistant mode.');
        }

        $prompt = trim((string) $data->get_param('prompt'));
        $currentQuery = trim((string) $data->get_param('current_query'));
        $errorMessage = trim((string) $data->get_param('error_message'));
        $connection = sanitize_text_field((string) $data->get_param('connection'));
        $model = (string) $data->get_param('model');

        if ($mode === 'generate' && $prompt === '') {
            throw new InvalidArgumentException('A prompt is required to generate a query.');
        }

        if (in_array($mode, ['fix', 'improve', 'explain'], true) && $currentQuery === '' && $prompt === '') {
            throw new InvalidArgumentException('Provide the current query and/or a prompt.');
        }

        // Cap inbound text so prompts stay bounded.
        $prompt = $this->truncate($prompt, 2000);
        $currentQuery = $this->truncate($currentQuery, 8000);
        $errorMessage = $this->truncate($errorMessage, 1500);

        $schemaContext = $this->schema->getContext($connection, $currentQuery);

        $built = $this->prompts->queryAssistantPrompt(
            $mode,
            $prompt,
            $currentQuery,
            $errorMessage,
            $schemaContext
        );

        $parsed = $this->ai->generateJson($built['system'], $built['user'], self::MAX_TOKENS, $model);

        $sql = $this->validator->sanitizeSql((string) ($parsed['sql'] ?? ''));
        $explanation = isset($parsed['explanation'])
            ? sanitize_textarea_field((string) $parsed['explanation'])
            : '';

        $warnings = [];
        foreach ((array) ($parsed['warnings'] ?? []) as $warning) {
            if (is_string($warning) && $warning !== '') {
                $warnings[] = sanitize_text_field($warning);
            }
        }

        if ($mode !== 'explain' && $sql === '' && isset($parsed['sql']) && trim((string) $parsed['sql']) !== '') {
            $warnings[] = 'The AI returned SQL that was rejected as unsafe (only single SELECT / WITH queries are allowed).';
        }

        return new WP_REST_Response([
            'sql'         => $sql,
            'explanation' => $explanation,
            'warnings'    => $warnings,
            'mode'        => $mode,
        ], 200);
    }

    /**
     * @param string $text
     * @param int    $max
     * @return string
     */
    private function truncate(string $text, int $max): string
    {
        if (strlen($text) <= $max) {
            return $text;
        }

        return substr($text, 0, $max);
    }
}
