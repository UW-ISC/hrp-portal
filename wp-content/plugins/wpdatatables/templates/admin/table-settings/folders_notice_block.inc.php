<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php if (get_option('wdtDismissFoldersNotice') !== "yes") : ?>
    <div class="wdt-folders-upgrade-notice notice notice-info is-dismissible notice-success">
        <div class="row m-l-5 m-t-10">
            <i class="wpdt-icon-star-full m-r-5 m-b-5"
               style="color: #091D70;"></i><strong><?php esc_html_e('Available from Standard license', 'wpdatatables'); ?></strong>

            <p class="m-b-5"><?php esc_html_e('Organize Tables and Charts Using Folders/Categories. More info in our docs on this ', 'wpdatatables'); ?>
                <a rel="nofollow"
                   href="https://wpdatatables.com/documentation/table-features/folders-for-tables-and-charts/">link.</a>
            </p>
            <p class="m-b-5">
                <a href="https://wpdatatables.com/pricing/?utm_source=wpdt-admin&utm_medium=folders&utm_campaign=wpdt&utm_content=wpdt"
                   rel="nofollow" target="_blank"
                   class="btn btn-primary wdt-upgrade-btn m-l-5"><?php esc_html_e('Upgrade', 'wpdatatables'); ?></a>
            </p>

            <a class="wdt-dismiss-folders-btn wdt-dismiss wdt-other-btn"><?php esc_html_e("Never show again", "wpdatatables") ?></a>
        </div>
    </div>
<?php endif; ?>