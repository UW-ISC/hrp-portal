<?php defined('ABSPATH') or die('Access denied.'); ?>

<div class="col-sm-4 m-b-16 form-group wdt-add-data-source-field hidden">
    <i class='wpdt-icon-star-full m-r-5' style='color: #091D70;'></i>
    <h4 class="c-title-color m-b-2 opacity-6" style="display: inline-block">
        <?php esc_html_e('Update table data from files', 'wpdatatables'); ?>
        <i style="line-height: 20px;"></i>
    </h4>
    <div class="fg-line">
        <div class="select">
            <select class="form-control selectpicker" id="wdt-source-file-data"
                    autocomplete="off">
                <option selected
                        value><?php esc_html_e('Select an option', 'wpdatatables'); ?></option>
                <option value=""
                        data-content="<i class='wpdt-icon-star-full m-r-5' style='color: #091D70;'></i> <span class='opacity-6'><?php esc_html_e('Replace rows with source data', 'wpdatatables'); ?></span>"></option>
                <option value=""
                        data-content="<i class='wpdt-icon-star-full m-r-5' style='color: #091D70;'></i> <span class='opacity-6'><?php esc_html_e('Add rows to the current table data', 'wpdatatables'); ?></span>"></option>
                <option value=""
                        data-content="<i class='wpdt-icon-star-full m-r-5' style='color: #091D70;'></i> <span class='opacity-6'><?php esc_html_e('Replace the entire table with the source', 'wpdatatables'); ?></span>"></option>
            </select>
        </div>
    </div>
</div>

<div class="col-sm-4 m-b-16 form-group wdt-add-data-source-field hidden">
    <i class='wpdt-icon-star-full m-r-5' style='color: #091D70;'></i>
    <h4 class="c-title-color m-b-2 opacity-6" style="display: inline-block">
        <?php esc_html_e('Available from Standard licence', 'wpdatatables'); ?>
        <i style="line-height: 20px;"></i>
    </h4>
    <div class="fg-line">
        <input type="text" class="hidden" hidden id="wdt-add-data-source-input" value="">
        <a class="di-block btn btn-primary wdt-upgrade-btn w-100"
           href="https://wpdatatables.com/pricing/?utm_source=wpdt-admin&utm_medium=update-manual&utm_campaign=wpdt&utm_content=wpdt"
           rel="nofollow" target="_blank"><?php esc_html_e('Upgrade', 'wpdatatables'); ?></a>
    </div>
</div>