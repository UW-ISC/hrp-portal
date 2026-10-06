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
 * POST /wpdatatables/v1/ai/query-constructor — Query Constructor Assistant (Feature 2).
 *
 * Helps admins describe a report in plain English and returns structured hints
 * (tables, columns, joins, conditions) for the WP / MySQL GUI query builders.
 *
 * @package WPDataTables\Controllers\Rest\Ai
 */
class QueryConstructorController extends Controller
{
    /** Output-token cap for query constructor suggestions. */
    const MAX_TOKENS = 1200;

    /** @var array<int, string> */
    const BUILDER_TYPES = ['wp', 'mysql'];

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
        $builderType = sanitize_key((string) $data->get_param('builder_type'));
        if (!in_array($builderType, self::BUILDER_TYPES, true)) {
            throw new InvalidArgumentException('Invalid query constructor builder type.');
        }

        $description = trim((string) $data->get_param('description'));
        if ($description === '') {
            throw new InvalidArgumentException('A description is required.');
        }

        $connection = sanitize_text_field((string) $data->get_param('connection'));
        $model      = (string) $data->get_param('model');

        $description = $this->truncate($description, 2000);

        $schemaContext = $this->schema->getContextForBuilder($builderType, $connection, $description);
        $postTypes     = $builderType === 'wp' ? array_values(get_post_types()) : [];

        $built = $this->prompts->queryConstructorPrompt(
            $builderType,
            $description,
            $schemaContext,
            $postTypes
        );

        $parsed = $this->ai->generateJson($built['system'], $built['user'], self::MAX_TOKENS, $model);

        $normalised = $this->validator->normalizeQueryConstructorOutput(
            $parsed,
            $schemaContext,
            $builderType,
            $postTypes
        );

        $generatedSql = $this->validator->sanitizeSql((string) ($normalised['generated_sql'] ?? ''));
        if ($generatedSql === '' && isset($parsed['generated_sql']) && trim((string) $parsed['generated_sql']) !== '') {
            $normalised['warnings'][] = 'The AI returned SQL that was rejected as unsafe (only single SELECT / WITH queries are allowed).';
        }
        $normalised['generated_sql'] = $generatedSql;
        $normalised['builder_type']  = $builderType;

        return new WP_REST_Response($normalised, 200);
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
