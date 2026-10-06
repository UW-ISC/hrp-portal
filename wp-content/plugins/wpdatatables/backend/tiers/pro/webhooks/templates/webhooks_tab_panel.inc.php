<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php
use WDTIntegration\Webhooks\WebhooksListTable;

$tableId = isset($_GET['table_id']) ? absint($_GET['table_id']) : 0;
?>

<div role="tabpanel" class="tab-pane fade" id="webhooks-settings">
    <div class="row">
        <div class="col-sm-12 m-b-15">
            <h4 class="c-black m-b-10">
                <?php esc_html_e('Webhooks', 'wpdatatables'); ?>
                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php esc_attr_e('Send HTTP requests when table rows are created, updated, deleted, or bulk-imported.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="col-sm-6 p-l-0">
                <p class="m-b-10">
                    <?php esc_html_e('Configure unlimited outbound webhooks for this table. Delivery is asynchronous and retries on failure.', 'wpdatatables'); ?>
                </p>
            </div>
            <div class="col-sm-6 text-right p-r-0">
                <button type="button" class="btn btn-primary" id="wdt-add-webhook"
                    <?php disabled($tableId < 1); ?>>
                    <i class="wpdt-icon-plus"></i>
                    <?php esc_html_e('Add webhook', 'wpdatatables'); ?>
                </button>
            </div>
        </div>
    </div>

    <div id="wdt-webhooks-list-container">
        <?php if ($tableId < 1) : ?>
            <div class="alert alert-info">
                <?php esc_html_e('Save the table first to manage webhooks.', 'wpdatatables'); ?>
            </div>
        <?php else :
            $webhooksTable = new WebhooksListTable(array('table_id' => $tableId));
            $webhooksTable->prepare_items();
            include __DIR__ . '/webhooks_table_list.inc.php';
        endif; ?>
    </div>

    <?php include __DIR__ . '/webhooks_modals.inc.php'; ?>
</div>
