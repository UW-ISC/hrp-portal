<?php defined('ABSPATH') or die('Access denied.'); ?>

<div class="col-sm-4 m-b-16 form-group wdt-add-data-source-field hidden">
    <h4 class="c-title-color m-b-2">
        <?php esc_html_e('Select how to use source file data', 'wpdatatables'); ?>
        <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
           title=""
           data-original-title="<?php esc_attr_e('If you\'re using the same column headers, choose if you want to replace existing data with source data or add source data to existing table data, or if you\'re using a completely different table and want to replace table data with source file data.', 'wpdatatables'); ?>"></i>
    </h4>
    <div class="fg-line">
        <div class="select">
            <select class="form-control selectpicker" id="wdt-source-file-data"
                    title="<?php esc_html_e('Select an option', 'wpdatatables'); ?>">
            <option value="replaceTableData"><?php esc_html_e('Replace rows with source data', 'wpdatatables'); ?></option>
            <option value="addDataToTable"><?php esc_html_e('Add rows to the current table data', 'wpdatatables'); ?></option>
            <option value="replaceTable"><?php esc_html_e('Replace the entire table with the source', 'wpdatatables'); ?></option>
            </select>
        </div>
    </div>
</div>

<div class="col-sm-4 m-b-16 wdt-source-file-path hidden">

    <h4 class="c-title-color m-b-2">
        <?php esc_html_e('Source file path or URL', 'wpdatatables'); ?>
        <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
           title=""
           data-original-title="<?php esc_attr_e('Upload your file or provide the full URL here. For CSV or Excel input sources only URLs or paths from same servers are supported. For Google Spreadsheets: please do not forget to publish the spreadsheet before pasting the URL.', 'wpdatatables'); ?>"></i>
    </h4>

    <div class="form-group">
        <div class="col-sm-9 p-0 wdt-input-url-container">
            <input type="text" id="wdt-add-data-source-input"
                   class="form-control input-sm input-url-path"
                   placeholder="<?php esc_attr_e('Paste URL or path, or click Browse to choose', 'wpdatatables'); ?>">
        </div>
        <div class="col-sm-3 wdt-source-browse-container">
            <button class="btn bgm-blue" id="wdt-add-data-browse-button">
                <?php esc_html_e('Browse...', 'wpdatatables'); ?>
            </button>
        </div>
    </div>
</div>
