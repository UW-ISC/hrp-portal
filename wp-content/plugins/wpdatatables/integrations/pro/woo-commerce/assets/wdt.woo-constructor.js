let constructedWooCommerceData = {
    post_type: 'product',
    posts_per_page: -1,
    tax_query: {
        relation: 'AND'
    },
    meta_query: {
        relation: 'AND'
    },
    customFieldColumns: {}
};

const tax_query_parameters = ['product_type', 'product_tag', 'product_cat', 'product_visibility'];
const meta_query_parameters = ['_sku', 'total_sales', '_stock', '_stock_status', '_backorders',
    '_wc_average_rating', '_wc_review_count', '_upsell_ids', '_crosssell_ids', '_width', '_length', '_weight', '_height'];
const price_meta_query_parameters = ['_price', '_regular_price', '_sale_price', '_price_operator', '_regular_price_operator', '_sale_price_operator'];
const metaQueryMappings = {
    '_sku': {compare: '=', type: 'CHAR'},
    'total_sales': {compare: '=', type: 'NUMERIC'},
    '_stock': {compare: '>=', type: 'NUMERIC'},
    '_stock_status': {compare: '=', type: 'CHAR'},
    '_backorders': {compare: '=', type: 'CHAR'},
    '_visibility': {compare: 'IN', type: 'CHAR'},
    '_wc_average_rating': {compare: '=', type: 'NUMERIC'},
    '_wc_review_count': {compare: '=', type: 'NUMERIC'},
    '_upsell_ids': {compare: 'REGEXP', type: 'CHAR'},
    '_crosssell_ids': {compare: 'REGEXP', type: 'CHAR'},
    '_width': {compare: '=', type: 'NUMERIC'},
    '_length': {compare: '=', type: 'NUMERIC'},
    '_weight': {compare: '=', type: 'NUMERIC'},
    '_height': {compare: '=', type: 'NUMERIC'},
};

(function ($) {
    let wdtNonce = $('#wdtNonce').val();

    /**
     * Toggle the display of range inputs
     */
    $('.price-comparison-operator').on('change', function () {
        let target = $(this).data('target');
        let operator = $(this).val();
        let rangeContainer = $(`.wdt-woo-price-range-inputs[data-parent="${target}"]`);
        let singleInputContainer = $(`.wdt-custom-number-input[data-input="${target}"]`);

        if (operator === 'between') {
            rangeContainer.show();
            singleInputContainer.hide();
        } else {
            rangeContainer.hide();
            singleInputContainer.show();
        }
    });

    /**
     * Change the WP Posts Query preview
     */
    $('.wdt-woo-parameter').on('change', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        let inputValue = $(this).hasClass("toggle-switch") ? $(this).find('input').is(":checked") : $(this).val();
        let wooParameter = this.dataset.value;

        if (tax_query_parameters.includes(wooParameter)) {
            handleTaxParameter(inputValue, wooParameter);
        } else if (meta_query_parameters.includes(wooParameter)) {
            handleMetaParameter(inputValue, wooParameter);
        } else if (price_meta_query_parameters.includes(wooParameter)) {
            handlePriceParameter(inputValue, wooParameter);
        } else {
            constructedWooCommerceData[this.dataset.value] = inputValue;
        }

        if ($(this).hasClass('toggle-switch')) {
            $(this).find('input').prop('checked', inputValue);
        } else if ($(this).hasClass('selectpicker')) {
            $(this).val(inputValue).selectpicker('refresh');
        } else {
            $(this).val(inputValue)
        }

        renderWooTablePreview();
    })

    function renderWooTablePreview() {
        $.ajax({
            url: ajaxurl,
            type: 'post',
            dataType: 'json',
            data: {
                action: 'wpdatatables_generate_live_wp_posts_preview',
                queryData: constructedWooCommerceData,
                tableData: constructedTableData,
                wdtNonce: wdtNonce
            },
            success: function (data) {
                $('.wdt-wp-posts-query-preview pre').html(data.preview);
            },
            error: function (data) {
                $('#wdt-error-modal .modal-body')
                    .html('There was an error while trying to generate the query! ' + data.statusText + ' ' + data.responseText);
                $('#wdt-error-modal').modal('show');
                $('.wdt-preload-layer').animateFadeOut();
            }
        });
    }

    function handleTaxParameter(inputValue, wooParameter) {
        if (!inputValue || inputValue.length === 0) {
            // Clear the tax query if no input is selected
            delete constructedWooCommerceData.tax_query[wooParameter];
            return;
        }

        if (wooParameter === 'product_visibility') {
            handleProductVisibility(inputValue, wooParameter);
        } else {
            constructedWooCommerceData.tax_query[wooParameter] = {
                taxonomy: wooParameter,
                field: 'slug',
                terms: inputValue
            };
        }
    }


    function handleMetaParameter(inputValue, wooParameter) {
        if (inputValue.length === 0 || inputValue === '') {
            delete constructedWooCommerceData.meta_query[wooParameter];
            return;
        }

        const metaQuery = metaQueryMappings[wooParameter];
        const {compare, type} = metaQuery;

        // Handle selectbox options as comma-separated string
        if (Array.isArray(inputValue)) {
            inputValue = inputValue.join(',');
        }

        if (compare === 'REGEXP' && (wooParameter === '_upsell_ids' || wooParameter === '_crosssell_ids')) {
            const ids = inputValue.split(',').map(id => id.trim());
            let regexpPattern;
            if (ids.length === 1) {
                // Match the ID exactly with boundaries around it
                regexpPattern = `(^|[^0-9])${ids[0]}([^0-9]|$)`;
            } else {
                // Match any of the IDs exactly with boundaries around each
                regexpPattern = ids.map(id => `(^|[^0-9])${id}([^0-9]|$)`).join('|');
            }

            constructedWooCommerceData.meta_query[wooParameter] = {
                key: wooParameter,
                value: regexpPattern,
                compare: 'REGEXP',
                type: 'CHAR'
            };
        } else if (inputValue.includes(',')) {
            // Handle multiple comma-separated values for other cases
            const ids = inputValue.split(',').map(id => id.trim());
            constructedWooCommerceData['meta_query'][wooParameter] = {
                key: wooParameter,
                value: ids,
                compare: compare,
                type: type
            };
        } else {
            constructedWooCommerceData.meta_query[wooParameter] = {
                key: wooParameter,
                value: inputValue,
                compare: compare,
                type: type
            };
        }
    }

    function handlePriceParameter(inputValue, wooParameter) {
        if (inputValue.length === 0 || inputValue === '') {
            delete constructedWooCommerceData.meta_query[wooParameter];
            return;
        }

        if (wooParameter.endsWith('_operator')) {
            handlePriceOperatorChange(inputValue, wooParameter);
        } else {
            handlePriceChange(inputValue, wooParameter);
        }
    }

    function handlePriceOperatorChange(operator, wooParameter) {
        let priceParameter = wooParameter.replace(/_operator$/, '');
        if (operator === 'between') {
            const minPrice = $(`.wdt-woo-price-range-inputs[data-parent="${priceParameter}"] .price-range-min`).val();
            const maxPrice = $(`.wdt-woo-price-range-inputs[data-parent="${priceParameter}"] .price-range-max`).val();

            if (minPrice && maxPrice) {
                constructedWooCommerceData.meta_query[priceParameter] = {
                    key: priceParameter,
                    value: [parseFloat(minPrice), parseFloat(maxPrice)],
                    compare: 'BETWEEN',
                    type: 'NUMERIC'
                };
            } else {
                delete constructedWooCommerceData.meta_query[priceParameter];
            }
        } else {
            const price = $(`input[data-value="${priceParameter}"]`).val();
            constructedWooCommerceData.meta_query[priceParameter] = {
                key: priceParameter,
                value: parseFloat(price),
                compare: operator,
                type: 'NUMERIC'
            };
        }
    }

    function handlePriceChange(inputValue, wooParameter) {
        let operatorSelector = $(`.price-comparison-operator[data-target="${wooParameter}"]`);
        let operator = operatorSelector.val();

        if (operator === 'between') {
            const minPrice = $(`.wdt-woo-price-range-inputs[data-parent="${wooParameter}"] .price-range-min`).val();
            const maxPrice = $(`.wdt-woo-price-range-inputs[data-parent="${wooParameter}"] .price-range-max`).val();

            if (minPrice && maxPrice) {
                constructedWooCommerceData.meta_query[wooParameter] = {
                    key: wooParameter,
                    value: [parseFloat(minPrice), parseFloat(maxPrice)],
                    compare: 'BETWEEN',
                    type: 'NUMERIC'
                };
            } else {
                delete constructedWooCommerceData.meta_query[wooParameter];
            }
        } else {
            constructedWooCommerceData.meta_query[wooParameter] = {
                key: wooParameter,
                value: parseFloat(inputValue),
                compare: operator,
                type: 'NUMERIC'
            };
        }
    }

    function handleProductVisibility(inputValue, wooParameter) {
        // Clear the tax query if "Visible" is chosen
        if (!inputValue.length || !inputValue[0]) {
            delete constructedWooCommerceData.tax_query[wooParameter];
            return;
        }

        const taxQueryConditions = inputValue.map((value) => {
            if (value === 'exclude-from-catalog exclude-from-search') {
                // "Hidden" requires both terms, so use "AND"
                return {
                    taxonomy: wooParameter,
                    field: 'name',
                    terms: ['exclude-from-catalog', 'exclude-from-search'],
                    operator: 'AND'
                };
            } else {
                return {
                    taxonomy: wooParameter,
                    field: 'name',
                    terms: [value],
                    operator: 'IN'
                };
            }
        });

        // Apply "OR" relation for multiple selected options
        if (taxQueryConditions.length > 1) {
            constructedWooCommerceData.tax_query = {
                relation: 'OR',
                ...taxQueryConditions,
            };
        } else {
            constructedWooCommerceData.tax_query[wooParameter] = taxQueryConditions[0];
        }
    }

    /**
     * Add new Custom Field Column
     */
    $('button.wdt-woo-commerce-add-cf-column').on('click', function (e) {
        e.preventDefault();
        cf_column_counter++;

        let cfBlockTemplate = $.templates("#wdt-woo-commerce-cf-template");
        let cfBlockHtml = cfBlockTemplate.render({cfColumnId: cf_column_counter});
        $(cfBlockHtml).appendTo('div#wdt-woo-commerce-cf-container').animateFadeIn();

    });

    /**
     * Remove Custom Field Column
     */
    $(document).on('click', '#wdt-constructor-delete-woo-cf-column', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        let cfColumnId = $(this).closest('.wdt-woo-commerce-cf-template').find('.wdt_woo_commerce_cf_parameter').data('count') - 1;

        if (constructedWooCommerceData['customFieldColumns'] && constructedWooCommerceData['customFieldColumns'][cfColumnId]) {
            delete constructedWooCommerceData['customFieldColumns'][cfColumnId];
        }
        if (constructedWooCommerceData['customFieldColumns']) {
            let updatedEntries = {};

            for (let key in constructedWooCommerceData['customFieldColumns']) {
                updatedEntries[key] = constructedWooCommerceData['customFieldColumns'][key];
            }
            constructedWooCommerceData['customFieldColumns'] = updatedEntries;
        }
        cf_column_counter--;
        $(this).closest('.wdt-wp-query-cf-template').remove();

        renderWooTablePreview();
    });

    /**
     * Change the Custom Field Column parameters
     */
    $(document).on('input change', '.wdt_woo_commerce_cf_parameter', function () {
        let value = $(this).val();
        let key = this.dataset.value;
        let count = this.dataset.count;

        if (!constructedWooCommerceData.customFieldColumns[count]) {
            constructedWooCommerceData.customFieldColumns[count] = {};
        }

        constructedWooCommerceData.customFieldColumns[count][key] = value;

        renderWooTablePreview();
    });

    /**
     * Switch tabs in settings
     */
    $('.main-wdt-woo-settings ul li a').on('click', function (e) {
        e.preventDefault();

        if ($(this).closest('ul').hasClass('main-wdt-woo-settings-ul')) {
            if ($(this)[0].hash === '#general-settings-tab') {
                $('.main-wdt-woo-settings .tab-content .fade.in.active').removeClass(' active in');
                $('.main-wdt-woo-settings .tab-content .tab-pane.in.active').removeClass(' active in');
            }
            $('.main-wdt-woo-settings-ul #general-settings-tab').addClass('active in');
        }
    });

    $('.main-wdt-woo-settings .tab-nav a').on('click', function (e) {
        e.preventDefault();
        $('#main-wdt-woo-settings ' + $(this)[0].hash).addClass('active in');
    });

})(jQuery);