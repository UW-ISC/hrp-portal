<?php defined('ABSPATH') or die('Access denied.'); ?>

<div role="tabpanel" class="tab-pane fade" id="webhooks-settings">
    <div class="row text-center">
        <i class="wpdt-icon-star-full m-r-5" style="color: #091D70;"></i>
        <strong><?php esc_html_e('Available from Pro license', 'wpdatatables'); ?></strong>

        <p class="m-b-0 m-t-10">
            <?php esc_html_e('Send unlimited outbound HTTP webhooks when table rows are created, updated, deleted, or bulk-imported.', 'wpdatatables'); ?>
        </p>
        <p class="m-b-0">
            <?php esc_html_e('Configure destination URLs, HTTP methods, signing secrets, and delivery status tracking per table.', 'wpdatatables'); ?>
        </p>
        <p class="p-t-10">
            <a href="https://wpdatatables.com/pricing/?utm_source=wpdt-admin&utm_medium=webhooks&utm_campaign=wpdt&utm_content=wpdt"
               rel="nofollow" class="btn btn-primary wdt-upgrade-btn"
               target="_blank"><?php esc_html_e('Upgrade', 'wpdatatables'); ?></a>
        </p>
    </div>
</div>
