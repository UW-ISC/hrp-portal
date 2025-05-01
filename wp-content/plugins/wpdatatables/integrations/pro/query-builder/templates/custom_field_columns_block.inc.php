<script id="wdt-wp-query-cf-template" type="text/x-jsrender">
    <div class="card-body bg-white wdt-wp-query-cf-template">
        <div class="row ">
            <div class="col-sm-2-0 m-b-16">
                <h4 class="c-title-color m-b-2">
    <?php esc_html_e('Column Header', 'wpdatatables'); ?>
    <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
       title="<?php esc_attr_e('Enter the header', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="fg-line form-group m-b-0">
                <input id="wdt-wp-query-cf-header-{{>cfColumnId}}" data-count="{{>cfColumnId}}" type="text"
                       class="form-control input-sm wdt_wp_query_cf_parameter" data-value="column_header"
                       placeholder="<?php esc_attr_e('Column Header', 'wpdatatables'); ?>" value="<?php esc_attr_e('New column', 'wpdatatables'); ?>">
            </div>
        </div>

        <div class="col-sm-2-0 m-b-16">
            <h4 class="c-title-color m-b-2">
    <?php esc_html_e('Custom Field', 'wpdatatables'); ?>
    <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
       title="<?php esc_attr_e('Specify the name of the custom field', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="fg-line form-group m-b-0">
                <input id="wdt-wp-query-cf-{{>cfColumnId}}" data-count={{>cfColumnId}} type="text"
                       class="form-control input-sm wdt_wp_query_cf_parameter" data-value="cf"
                       placeholder="<?php esc_attr_e('Custom Field', 'wpdatatables'); ?>">
            </div>
        </div>
            <div class="col-sm-1 p-r-0 p-l-0 text-center">
                <ul class="actions p-r-5">
                    <li class="p-t-30" id="wdt-constructor-delete-cf-column">
                        <a>
                            <i class="wpdt-icon-trash"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</script>