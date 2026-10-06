<?php

namespace WDTIntegration\Webhooks;

defined('ABSPATH') or die('Access denied.');

/**
 * Builds outbound webhook JSON/form payloads.
 */
class WebhookPayloadBuilderService
{
    /**
     * @param string $event
     * @param int $tableId
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function build($event, $tableId, array $context = array())
    {
        $tableMeta = $this->resolveTableMeta($tableId, $context);

        $payload = array(
            'event' => $event,
            'event_id' => $this->generateEventId($event, $tableId),
            'timestamp' => gmdate('c'),
            'table' => $tableMeta,
            'meta' => array(
                'source' => isset($context['source']) ? (string) $context['source'] : 'unknown',
                'user_id' => isset($context['user_id']) ? (int) $context['user_id'] : get_current_user_id(),
            ),
        );

        if ($event === 'rows.bulk_imported') {
            $rowIds = isset($context['row_ids']) && is_array($context['row_ids']) ? array_values($context['row_ids']) : array();
            $rows = isset($context['rows']) && is_array($context['rows']) ? array_values($context['rows']) : array();
            $payload['rows'] = array(
                'count' => isset($context['count']) ? (int) $context['count'] : max(count($rowIds), count($rows)),
                'row_ids' => $rowIds,
                'sample' => array_slice($rows, 0, 5),
            );
        } else {
            $payload['row'] = array(
                'id' => isset($context['row_id']) ? $context['row_id'] : null,
                'data' => isset($context['row_data']) && is_array($context['row_data']) ? $context['row_data'] : array(),
            );

            if (!empty($context['changed_columns']) && is_array($context['changed_columns'])) {
                $payload['meta']['changed_columns'] = array_values($context['changed_columns']);
            }
        }

        return $payload;
    }

    /**
     * Lightweight sample payload for the Test action.
     *
     * @param array<string, mixed> $webhook
     * @return array<string, mixed>
     */
    public function buildTestPayload(array $webhook)
    {
        return $this->build(
            $webhook['event'],
            (int) $webhook['table_id'],
            array(
                'source' => 'webhook_test',
                'user_id' => get_current_user_id(),
                'row_id' => 0,
                'row_data' => array(
                    'example_column' => 'example_value',
                ),
                'row_ids' => array(1, 2, 3),
                'count' => 3,
                'rows' => array(
                    array('id' => 1, 'data' => array('example_column' => 'row_1')),
                    array('id' => 2, 'data' => array('example_column' => 'row_2')),
                ),
            )
        );
    }

    /**
     * @param int $tableId
     * @param array<string, mixed> $context
     * @return array{id:int,title:string,type:string}
     */
    private function resolveTableMeta($tableId, array $context)
    {
        if (!empty($context['table']) && is_array($context['table'])) {
            return array(
                'id' => (int) $tableId,
                'title' => isset($context['table']['title']) ? (string) $context['table']['title'] : '',
                'type' => isset($context['table']['type']) ? (string) $context['table']['type'] : '',
            );
        }

        $title = '';
        $type = '';

        if (class_exists('WDTConfigController')) {
            try {
                $tableData = \WDTConfigController::loadTableFromDB((int) $tableId, false);
                if ($tableData) {
                    $title = isset($tableData->title) ? (string) $tableData->title : '';
                    $type = isset($tableData->table_type) ? (string) $tableData->table_type : '';
                }
            } catch (\Exception $e) {
                // Leave empty meta on failure.
            }
        }

        return array(
            'id' => (int) $tableId,
            'title' => $title,
            'type' => $type,
        );
    }

    /**
     * @param string $event
     * @param int $tableId
     * @return string
     */
    private function generateEventId($event, $tableId)
    {
        return wp_generate_uuid4();
    }
}
