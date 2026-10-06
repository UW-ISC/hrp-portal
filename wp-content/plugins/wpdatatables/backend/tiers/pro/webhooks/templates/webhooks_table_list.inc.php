<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php /** @var \WDTIntegration\Webhooks\WebhooksListTable $webhooksTable */ ?>

<div class="container wdt-webhooks-toolbar">
    <div class="row">
        <div class="col-xs-6">
            <div class="bulk-action-container">
                <?php $webhooksTable->display_tablenav('top'); ?>
            </div>
        </div>
    </div>
</div>

<table class="wp-list-table <?php echo esc_attr(implode(' ', $webhooksTable->get_table_classes())); ?>">
    <thead>
    <tr>
        <?php $webhooksTable->print_column_headers(); ?>
    </tr>
    </thead>
    <tbody id="the-list" data-wp-lists="list:webhook">
    <?php $webhooksTable->display_rows_or_placeholder(); ?>
    </tbody>
</table>
