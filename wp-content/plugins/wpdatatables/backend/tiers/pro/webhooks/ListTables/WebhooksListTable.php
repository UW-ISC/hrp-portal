<?php

namespace WDTIntegration\Webhooks;

defined('ABSPATH') or die('Access denied.');

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Webhooks list table for the per-table Webhooks settings tab.
 */
class WebhooksListTable extends \WP_List_Table
{
    /** @var int */
    private $tableId = 0;

    /** @var WebhookManagerService */
    private $manager;

    /**
     * @param array<string, mixed> $args
     */
    public function __construct($args = array())
    {
        parent::__construct(
            array(
                'singular' => 'webhook',
                'plural' => 'webhooks',
                'ajax' => false,
                'primary' => 'name',
            )
        );

        $this->tableId = isset($args['table_id']) ? absint($args['table_id']) : 0;
        $this->manager = isset($args['manager']) && $args['manager'] instanceof WebhookManagerService
            ? $args['manager']
            : new WebhookManagerService();
    }

    /**
     * @return array<string, string>
     */
    public function get_columns()
    {
        return array(
            'name' => __('Name', 'wpdatatables'),
            'url' => __('URL', 'wpdatatables'),
            'event' => __('Event', 'wpdatatables'),
            'method' => __('Method', 'wpdatatables'),
            'last_status' => __('Last status', 'wpdatatables'),
            'enabled' => __('Enabled', 'wpdatatables'),
            'actions' => '',
        );
    }

    /**
     * @return array<string, array{0:string,1:bool}>
     */
    public function get_sortable_columns()
    {
        return array(
            'name' => array('name', false),
            'event' => array('event', false),
            'method' => array('method', false),
        );
    }

    /**
     * @return string[]
     */
    public function get_table_classes()
    {
        return array('widefat', 'fixed', 'striped', 'wdt-webhooks-table');
    }

    /**
     * @param array<string, mixed> $item
     * @param string $column_name
     * @return string
     */
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'name':
                return '<strong>' . esc_html($item['name']) . '</strong>';
            case 'url':
                $url = (string) $item['url'];
                $display = strlen($url) > 60 ? substr($url, 0, 57) . '…' : $url;
                return '<span class="wdt-webhook-url" title="' . esc_attr($url) . '">' . esc_html($display) . '</span>';
            case 'event':
                $labels = WebhookManagerService::eventLabels();
                $label = isset($labels[$item['event']]) ? $labels[$item['event']] : $item['event'];
                return esc_html($label);
            case 'method':
                return '<code>' . esc_html($item['method']) . '</code>';
            case 'last_status':
                $status = !empty($item['last_status']) ? $item['last_status'] : '—';
                $runAt = !empty($item['last_run_at']) ? $item['last_run_at'] : '';
                $html = '<span class="wdt-webhook-last-status">' . esc_html($status) . '</span>';
                if ($runAt !== '') {
                    $html .= '<br/><small class="text-muted">' . esc_html($runAt) . '</small>';
                }
                return $html;
            case 'enabled':
                $checked = !empty($item['enabled']) ? 'checked' : '';
                return '<div class="toggle-switch" data-ts-color="blue">'
                    . '<input type="checkbox" class="wdt-webhook-enabled-toggle" data-id="'
                    . esc_attr((string) $item['id']) . '" ' . $checked . ' />'
                    . '<label class="ts-label"></label>'
                    . '</div>';
            case 'actions':
                return '<div class="wdt-function-flex">'
                    . '<a href="#" class="wdt-edit-webhook" data-id="' . esc_attr((string) $item['id'])
                    . '" data-toggle="tooltip" title="' . esc_attr__('Edit', 'wpdatatables')
                    . '"><i class="wpdt-icon-pen"></i></a>'
                    . '<a href="#" class="wdt-test-webhook" data-id="' . esc_attr((string) $item['id'])
                    . '" data-toggle="tooltip" title="' . esc_attr__('Test', 'wpdatatables')
                    . '"><i class="wpdt-icon-play"></i></a>'
                    . '<a href="#" class="wdt-delete-webhook" data-id="' . esc_attr((string) $item['id'])
                    . '" data-toggle="tooltip" title="' . esc_attr__('Delete', 'wpdatatables')
                    . '"><i class="wpdt-icon-trash"></i></a>'
                    . '</div>';
            default:
                return isset($item[$column_name]) ? esc_html((string) $item[$column_name]) : '';
        }
    }

    /**
     * @param string $which
     * @return void
     */
    public function display_tablenav($which)
    {
        if ('top' === $which) {
            wp_nonce_field('bulk-' . $this->_args['plural']);
        }
        echo '<div class="tablenav ' . esc_attr($which) . '">';
        $this->extra_tablenav($which);
        echo '<br class="clear"/>';
        echo '</div>';
    }

    /**
     * @return void
     */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $data = $this->tableId > 0 ? $this->manager->listForTable($this->tableId) : array();

        $orderby = isset($_REQUEST['orderby']) ? sanitize_text_field(wp_unslash($_REQUEST['orderby'])) : 'id';
        $order = isset($_REQUEST['order']) && strtolower(sanitize_text_field(wp_unslash($_REQUEST['order']))) === 'desc'
            ? 'desc'
            : 'asc';

        if (in_array($orderby, array('id', 'name', 'event', 'method'), true)) {
            usort(
                $data,
                static function ($a, $b) use ($orderby, $order) {
                    $av = isset($a[$orderby]) ? $a[$orderby] : '';
                    $bv = isset($b[$orderby]) ? $b[$orderby] : '';
                    if ($orderby === 'id') {
                        $cmp = (int) $av <=> (int) $bv;
                    } else {
                        $cmp = strcasecmp((string) $av, (string) $bv);
                    }
                    return $order === 'desc' ? -$cmp : $cmp;
                }
            );
        }

        $perPage = get_option('wdtTablesPerPage') ? (int) get_option('wdtTablesPerPage') : 10;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->items = array_slice($data, ($currentPage - 1) * $perPage, $perPage);
        $this->set_pagination_args(
            array(
                'total_items' => $totalItems,
                'per_page' => $perPage,
                'total_pages' => (int) ceil($totalItems / max(1, $perPage)),
            )
        );
    }

    /**
     * Render tbody rows HTML only (for AJAX refresh).
     *
     * @return string
     */
    public function getRowsHtml()
    {
        $this->prepare_items();
        ob_start();
        $this->display_rows_or_placeholder();
        return (string) ob_get_clean();
    }
}
