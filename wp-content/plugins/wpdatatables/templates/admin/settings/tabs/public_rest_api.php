<?php defined('ABSPATH') or die('Access denied.'); ?>

<div role="tabpanel" class="tab-pane" id="wdt-public-api-settings">
    <div class="row">
        <div class="wdt-public-api-not-available-notice"
             style="max-width: 1440px;margin: 0 auto;padding: 10px;text-align: center;">
            <h4 class="f-14">
                <i class="wpdt-icon-star-full m-r-5" style="color: #091D70;"></i>
                <?php esc_html_e('Available from Developer licence', 'wpdatatables'); ?></h4>
            <p class="m-b-0"><?php esc_html_e('Expose read and write access to your tables and charts for external integrations through the public REST API.', 'wpdatatables'); ?></p>
            <p><?php esc_html_e('Generate scoped API keys with read, edit and delete permissions, set their expiration, and revoke them at any time.', 'wpdatatables'); ?></p>
            <a rel="nofollow" target="_blank" class="btn btn-primary wdt-upgrade-btn m-b-20"
               href="https://wpdatatables.com/pricing/?utm_source=wpdt-pro&utm_medium=upgrade&utm_content=wpdt&utm_campaign=wpdt"> <?php esc_html_e('Upgrade', 'wpdatatables'); ?></a>
        </div>
    </div>
</div>
