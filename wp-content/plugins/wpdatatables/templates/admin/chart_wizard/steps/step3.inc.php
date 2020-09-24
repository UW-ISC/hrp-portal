<?php defined('ABSPATH') or die('Access denied.'); ?>

<div class="col-sm-12 p-0 wdt-chart-column-picker-container">

    <div class="existing-columns card m-t-15 m-b-15 col-sm-5-5">
        <div class="card-header ch-alt">
            <h2><?php _e('Columns in the data source', 'wpdatatables'); ?>
                <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php _e('Choose table columns that you would like to use in the chart. You can either drag the column blocks, or click to select them and use controls in the middle to add or remove from the chart.', 'wpdatatables'); ?>"></i>
            </h2>
            <button class="btn  select-all-columns pull-right"><?php _e('Select All', 'wpdatatables'); ?></button>
        </div>
        <div class="wdt-chart-wizart-existing-columns-container card-body card-padding">

        </div>
    </div>

    <div class="picker_column col-sm-0-4">
        <button class="btn bgm-gray " id="wdt-add-all-chart-columns" data-toggle="tooltip"
                data-original-title="<?php _e('Add all', 'wpdatatables'); ?>"></button>
        <button class="btn bgm-gray " id="wdt-add-chart-columns" data-toggle="tooltip"
                data-original-title="<?php _e('Add', 'wpdatatables'); ?>"></button>
        <button class="btn bgm-gray " id="wdt-remove-chart-columns" data-toggle="tooltip"
                data-original-title="<?php _e('Remove', 'wpdatatables'); ?>"></button>
        <button class="btn bgm-gray " id="wdt-remove-all-chart-columns" data-toggle="tooltip"
                data-original-title="<?php _e('Remove all', 'wpdatatables'); ?>"></button>
    </div>

    <div class="chosen_columns m-t-15 m-b-15 card col-sm-5-5">
        <div class="card-header ch-alt">
            <h2><?php _e('Columns used in the chart', 'wpdatatables'); ?>
                <button class="btn select-all-columns pull-right"><?php _e('Select All', 'wpdatatables'); ?></button>
            </h2>
        </div>
        <div class="wdt-chart-wizard-chosen-columns-container card-body card-padding">

            <div class="strings-error alert alert-danger m-b-10"
                 style="display:none"><i
                        class="wpdt-icon-exclamation-triangle"></i><?php _e('Please do not add more then one string-type (date/time, image, email, URL) column since only one can be used as a label', 'wpdatatables'); ?>
            </div>
            <div class="min-columns-error alert alert-danger m-b-10"
                 style="display:none"><i
                        class="wpdt-icon-exclamation-triangle"></i><?php _e('Minimum count of columns for this chart type is ', 'wpdatatables'); ?>
                <span class="columns"></span></div>
            <div class="max-columns-error alert alert-danger m-b-10"
                 style="display:none"><i
                        class="wpdt-icon-exclamation-triangle"></i><?php _e('Maximum count of columns for this chart type is ', 'wpdatatables'); ?>
                <span class="columns"></span></div>
        </div>
    </div>

</div>

<div class="col-sm-12 p-0">

    <div class="col-sm-5-5 p-l-0 p-r-0 data-filtering">
        <h4 class="c-title-color m-b-2">
            <?php _e('Charts data filtering', 'wpdatatables'); ?>
            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
               title="<?php _e('If you enable this, chart will automatically re-render with actual data every time you sort, filter, or switch pages in the table (chart must be in the same page with the table).', 'wpdatatables'); ?>"></i>
        </h4>
        <div class="toggle-switch p-b-16" data-ts-color="blue">
            <input id="follow-table-filtering" type="checkbox">
            <label for="follow-table-filtering"><?php _e('Follow table filtering', 'wpdatatables'); ?></label>
        </div>
    </div>
</div>
<div class="col-sm-12 p-0">
    <div class="col-sm-5-5 p-l-0 p-r-0">
        <h4 class="c-title-color m-b-2">
            <?php _e('Row range', 'wpdatatables'); ?>
            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
               title="<?php _e('Please choose the row range.', 'wpdatatables'); ?> <?php _e('If you do not want data from all the table rows to be in the chart, you can pick the row range manually. Please note that if the data set is large the range picker can load slowly or even cause an out of memory error.', 'wpdatatables'); ?>"></i>
        </h4>
        <div class="form-group m-0">
            <div class="fg-line">
                <div class="select">
                    <select class="selectpicker" name="wdt-chart-row-range-type" id="wdt-chart-row-range-type">
                        <option value="all_rows"><?php _e('All rows (default)', 'wpdatatables'); ?></option>
                        <option value="pick_rows"><?php _e('Pick range (slow on large datasets)', 'wpdatatables'); ?></option>
                    </select>
                </div>
            </div>
        </div>
        <div id="range_picked_info"><?php _e('Rows picked', 'wpdatatables'); ?>: <span
                    class="rowspicked"><?php _e('All', 'wpdatatables'); ?></span></div>
        <button class="btn btn-icon-text " id="open-range-picker-btn"
                style="display:none"><?php _e('Range picker...', 'wpdatatables'); ?></button>
        <br/>
    </div>

</div>
<div class="clear"></div>

<script id="wdt-chart-column-block" type="text/x-jsrender">
    {{for columns}}
        <div class="btn btn-default btn-block chart-column-block {{:column_type}} m-t-5" data-column_id="{{:id}}" data-orig_header="{{:orig_header}}"><strong>{{:display_header}}</strong> ({{:column_type}})</div>
    {{/for}}


</script>
