(function ($) {
    $(function () {
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

        function addClause(selector, templateId, containerId, counterVarName, relationContainerId, clauseId) {
            $(document).off('click', selector).on('click', selector, function (e) {
                e.preventDefault();

                // Increment counter and update variable
                window[counterVarName]++;
                let count = window[counterVarName];

                let blockHtml = $.templates(templateId).render({[clauseId]: count});
                $(blockHtml).appendTo(containerId).animateFadeIn();

                if (count > 1 && clauseId !== 'cfColumnId') {
                    $(relationContainerId).animateFadeIn();
                }
            });
        }

        function removeClause(documentSelector, deleteButtonSelector, counterVarName, relationContainerId, parameterId, queryType) {
            $(document).off('click', deleteButtonSelector).on('click', deleteButtonSelector, function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                let queryId = $(this).closest('.wdt-wp-query-clause-template').find('.' + parameterId).data('count');

                if (wpdatatable_config.queryParameters[queryType] && wpdatatable_config.queryParameters[queryType][queryId]) {
                    delete wpdatatable_config.queryParameters[queryType][queryId];
                }
                window[counterVarName]--;
                $(this).closest('.wdt-wp-query-clause-template').remove();
                if (wpdatatable_config.queryParameters[queryType]) {
                    let updatedEntries = {};
                    let index = 0;

                    for (let key in wpdatatable_config.queryParameters[queryType]) {
                        if (key !== 'relation') {
                            updatedEntries[index] = wpdatatable_config.queryParameters[queryType][key];
                            index++;
                        } else {
                            updatedEntries[key] = wpdatatable_config.queryParameters[queryType][key];
                        }
                    }

                    wpdatatable_config.queryParameters[queryType] = updatedEntries;
                }
                if (window[counterVarName] === 1) {
                    $(relationContainerId).animateFadeOut();
                }
            });
        }

        function removeCfClause(documentSelector, deleteButtonSelector, counterVarName, parameterId, queryType) {
            $(document).off('click', deleteButtonSelector).on('click', deleteButtonSelector, function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                let cfId = $(this).closest('.wdt-wp-query-cf-template').find('.' + parameterId).data('count');

                if (wpdatatable_config.queryParameters[queryType] && wpdatatable_config.queryParameters[queryType][cfId]) {
                    delete wpdatatable_config.queryParameters[queryType][cfId];
                }
                window[counterVarName]--;
                $(this).closest('.wdt-wp-query-cf-template').remove();
                if (wpdatatable_config.queryParameters[queryType]) {
                    let updatedEntries = {};

                    for (let key in wpdatatable_config.queryParameters[queryType]) {
                        updatedEntries[key] = wpdatatable_config.queryParameters[queryType][key];
                    }

                    wpdatatable_config.queryParameters[queryType] = updatedEntries;
                }
            });
        }

        function clearClauses(containerId) {
            $(containerId).empty();
        }

        function getQueryClauseLength(queryClause) {
            if (typeof queryClause === 'object' && queryClause !== null) {
                if (queryClause.hasOwnProperty('relation')) {
                    const keys = Object.keys(queryClause).filter(key => key !== 'relation');
                    return keys.length;
                } else {
                    return 1;
                }
            }
            return 0;
        }

        function updateRegularParameters(key, value) {
            let $input = $('[data-value="' + key + '"]');

            if (Array.isArray(value) || $input.hasClass('selectpicker')) {
                $input.each(function () {
                    $input.val(value).selectpicker('refresh');
                });
            } else if ($input.hasClass('toggle-switch')) {
                $input.find('input').prop('checked', value);
            } else {
                $input.val(value);
            }
        }

        function fillQueryParamsBack() {
            // Process the remaining meta_query parameters
            if (wpdatatable_config.queryParameters.meta_query) {
                $.each(wpdatatable_config.queryParameters.meta_query, function (key, param) {

                    // Skip 'relation' and price parameters
                    if (price_meta_query_parameters.includes(key)) {
                        return;
                    }

                    let $input = $('[data-value="' + key + '"]');
                    if (typeof param === 'object' && !Array.isArray(param)) {
                        // Handle crosssell_ids, upsell_ids, and other special object-based params
                        if (key === '_crosssell_ids' || key === '_upsell_ids') {
                            let ids = [];
                            if (param.value) {
                                ids = param.value.match(/\d+/g);

                                if (ids) {
                                    ids = ids.filter(id => id !== "0" && id !== "9");
                                    ids = [...new Set(ids)];
                                }
                                if (ids.length > 0) {
                                    $input.val(ids.join(',')).selectpicker('refresh');
                                }
                            }
                        } else if (param.value) {
                            if (Array.isArray(param.value) || $input.hasClass('selectpicker')) {
                                $input.each(function () {
                                    $input.val(param.value).selectpicker('refresh');
                                });
                            } else {
                                $input.val(param.value);
                            }
                        }
                    } else if (Array.isArray(param)) {
                        $input.each(function () {
                            $input.val(param).selectpicker('refresh');
                        });
                    } else {
                        $input.val(param);
                    }
                });
            }

            // Process for tax_query
            if (wpdatatable_config.queryParameters.tax_query) {
                $.each(wpdatatable_config.queryParameters.tax_query, function (key, param) {
                    let $input = $('[data-value="' + key + '"]');

                    if (typeof param === 'object' && !Array.isArray(param)) {
                        if (key === 'taxonomy') {
                            $input.val(param).selectpicker('refresh');
                        } else if (param.terms) {
                            if (Array.isArray(param.terms)) {
                                $input.each(function () {
                                    $input.val(param.terms).selectpicker('refresh');
                                });
                            } else {
                                $input.val(param.terms);
                            }
                        }
                    } else if (Array.isArray(param)) {
                        $input.each(function () {
                            $input.val(param).selectpicker('refresh');
                        });
                    } else {
                        $input.val(param);
                    }
                });
            }

            // Process for woo commerce prices
            price_meta_query_parameters
                .filter(key => wpdatatable_config.queryParameters.meta_query[key])
                .forEach(function (key) {
                    let param = wpdatatable_config.queryParameters.meta_query[key];

                    if (param && typeof param === 'object') {
                        if (param.value) {
                            // Handle "Between" operator with range inputs
                            let compare = param.compare.toLowerCase();
                            if (compare === 'between') {
                                const $minInput = $('[id*="wdt_woo' + key + '_min"]');
                                const $maxInput = $('[id*="wdt_woo' + key + '_max"]');
                                $(`.wdt-woo-price-range-inputs[data-parent="${key}"]`).show();
                                $(`.wdt-custom-number-input[data-input="${key}"]`).hide();
                                if (Array.isArray(param.value) && param.value.length === 2) {
                                    $minInput.val(param.value[0]);
                                    $maxInput.val(param.value[1]);
                                }
                            } else {
                                // Set value for standard single input
                                $('[data-value="' + key + '"]').val(param.value);
                            }
                            $('[data-value="' + key + '_operator"]').val(compare).selectpicker('refresh');
                        }
                    }
                });

        }


        /**
         * Extend wpdatatable_config object with new properties and methods
         */
        $.extend(wpdatatable_config, {
            hasServerSideIntegration: 1,
            showCartInformation: 1,
            queryParameters: {},
            populatePostQueryParameters: function () {
                wpdatatable_config.queryParameters = JSON.parse(wpdatatable_config.content ? wpdatatable_config.content : wpdatatable_init_config.content);

                tax_query_counter = getQueryClauseLength(wpdatatable_config.queryParameters.tax_query || {});
                meta_query_counter = getQueryClauseLength(wpdatatable_config.queryParameters.meta_query || {});
                date_query_counter = getQueryClauseLength(wpdatatable_config.queryParameters.date_query || {});
                cf_column_counter = Object.keys(wpdatatable_config.queryParameters.customFieldColumns).length;

                // Clear existing clauses
                clearClauses('div#wdt-wp-query-custom-fields-container');
                clearClauses('div#wdt-wp-query-tax-clause-container');
                clearClauses('div#wdt-wp-query-date-clause-container');
                clearClauses('div#wdt-wp-query-cf-container');

                // Loop the query parameters and fill all the inputs with the data
                $.each(wpdatatable_config.queryParameters, function (key, value) {
                    if (!['meta_query', 'tax_query', 'date_query', 'comment_count', 'customFieldColumns'].includes(key)) {
                        updateRegularParameters(key, value);
                    } else if (key === 'comment_count') {
                        let $compare_input = $('[data-value="comment_count_compare"]');
                        let $value_input = $('[data-value="comment_count_value"]');

                        $compare_input.val(value['compare']).selectpicker('refresh');
                        $value_input.val(value['value']);
                    } else if (key === 'customFieldColumns') {
                        wpdatatable_config.populateCustomFieldColumns();

                    } else {
                        wpdatatable_config.populateClause(key);
                    }
                });
            },

            populateWooCommerceParameters: function () {
                wpdatatable_config.queryParameters = JSON.parse(wpdatatable_config.content ? wpdatatable_config.content : wpdatatable_init_config.content);
                $.each(wpdatatable_config.queryParameters, function (key, value) {
                    updateRegularParameters(key, value);
                });
                fillQueryParamsBack();
            },

            updatePostQueryParameters: function () {
                wpdatatable_config.content = JSON.stringify(wpdatatable_config.queryParameters);
            },

            addRemoveClauseTemplates: function () {
                addClause('button.wdt-wp-query-add-custom-field', '#wdt-wp-query-meta-template', 'div#wdt-wp-query-custom-fields-container', 'meta_query_counter', '#wdt-wp-query-meta-relation-container', 'metaFieldId');
                removeClause(document, '#wdt-constructor-delete-custom-field', 'meta_query_counter', '#wdt-wp-query-meta-relation-container', 'wdt_wp_query_meta_parameter', 'meta_query');

                addClause('button.wdt-wp-query-add-tax-clause', '#wdt-wp-query-tax-template', 'div#wdt-wp-query-tax-clause-container', 'tax_query_counter', '#wdt-wp-query-tax-relation-container', 'taxClauseId');
                removeClause(document, '#wdt-constructor-delete-tax-clause', 'tax_query_counter', '#wdt-wp-query-tax-relation-container', 'wdt_wp_query_tax_parameter', 'tax_query');

                addClause('button.wdt-wp-query-add-date-clause', '#wdt-wp-query-date-template', 'div#wdt-wp-query-date-clause-container', 'date_query_counter', '#wdt-wp-query-date-relation-container', 'dateClauseId');
                removeClause(document, '#wdt-constructor-delete-date-clause', 'date_query_counter', '#wdt-wp-query-date-relation-container', 'wdt_wp_query_date_parameter', 'date_query');

                addClause('button.wdt-wp-query-add-cf-column', '#wdt-wp-query-cf-template', 'div#wdt-wp-query-cf-container', 'cf_column_counter', '', 'cfColumnId');
                removeCfClause(document, '#wdt-constructor-delete-cf-column', 'cf_column_counter', 'wdt_wp_query_cf_parameter', 'customFieldColumns');
            },

            populateClause: function (key) {
                let value = wpdatatable_config.queryParameters[key];
                let containerId, templateId, clauseId, relationContainer;

                switch (key) {
                    case 'meta_query':
                        containerId = 'div#wdt-wp-query-custom-fields-container';
                        templateId = '#wdt-wp-query-meta-template';
                        clauseId = 'metaFieldId';
                        relationContainer = '#wdt-wp-query-meta-relation-container';
                        break;
                    case 'tax_query':
                        containerId = 'div#wdt-wp-query-tax-clause-container';
                        templateId = '#wdt-wp-query-tax-template';
                        clauseId = 'taxClauseId';
                        relationContainer = '#wdt-wp-query-tax-relation-container';
                        break;
                    case 'date_query':
                        containerId = 'div#wdt-wp-query-date-clause-container';
                        templateId = '#wdt-wp-query-date-template';
                        clauseId = 'dateClauseId';
                        relationContainer = '#wdt-wp-query-date-relation-container';
                        break;
                }

                let template = $.templates(templateId);
                if (value && typeof value === 'object' && Object.keys(value).length > 1) {
                    let count = 0;
                    Object.entries(value).forEach(([index, clause]) => {
                        // Check if the entry is a relation or a clause
                        if (index === 'relation' && Object.keys(value).length > 2) {
                            // Set the relation dropdown or input for multiple clauses
                            $(`[data-id="wdt-wp-query-${key}-relation"]`).val(clause);
                            $(`#wdt-wp-query-${key}-relation`).val(clause).selectpicker('refresh');
                            $(relationContainer).animateFadeIn();
                            $(relationContainer).removeClass('hidden');
                        } else if (index == count) {
                            // Render and append the block for each clause
                            let blockHtml = template.render({[clauseId]: count});
                            $(blockHtml).appendTo(containerId).show();

                            // Populate clause fields
                            Object.entries(clause).forEach(([param, val]) => {
                                // Select the input based on data attributes
                                let $input = $(`[data-count="${count}"][data-value="${param}"]`);
                                if ($input.length) {
                                    if (Array.isArray(val)) {
                                        $input.val(val).selectpicker('refresh');
                                    } else if (typeof val === 'boolean') {
                                        $input.prop('checked', val);
                                    } else {
                                        $input.val(val);
                                    }
                                }
                            });
                            count++;
                        }
                    });
                } else if (Array.isArray(value)) {
                    value.forEach((clause, index) => {
                        let count = index + 1;

                        // Render and append the block
                        let blockHtml = template.render({[clauseId]: count});
                        $(blockHtml).appendTo(containerId).show();

                        // Populate clause fields
                        if (clause.relation) {
                            $(`[data-id="wdt-wp-query-${key}-relation"]`).val(clause.relation);
                            $(relationContainer).animateFadeIn();
                            $(relationContainer).removeClass('hidden');
                        }

                        Object.entries(clause).forEach(([param, val]) => {
                            let $input = $(`[data-count="${count}"][data-value="${param}"]`);
                            if ($input.length) {
                                if (Array.isArray(val)) {
                                    $input.val(val).selectpicker('refresh');
                                } else if (typeof val === 'boolean') {
                                    $input.prop('checked', val);
                                } else {
                                    $input.val(val);
                                }
                            }
                        });
                    });
                }
            },
            populateCustomFieldColumns: function () {
                let value = wpdatatable_config.queryParameters.customFieldColumns;
                let containerId = 'div#wdt-wp-query-cf-container';
                let templateId = '#wdt-wp-query-cf-template';
                let clauseId = 'cfColumnId';

                let template = $.templates(templateId);
                if (value && typeof value === 'object') {
                    Object.entries(value).forEach(([index, clause]) => {
                        let blockHtml = template.render({[clauseId]: index});
                        $(blockHtml).appendTo(containerId).show();

                        Object.entries(clause).forEach(([param, val]) => {
                            let $input = $(`[data-count="${index}"][data-value="${param}"]`);
                            if ($input.length) {
                                if (param === 'cf') {
                                    $input.val(val);
                                }

                                if (param === 'column_header') {
                                    // Update the display header and disable the CF Column Header input
                                    let orig_header = clause["cf"];
                                    if (wpdatatable_config.columns_by_headers && wpdatatable_config.columns_by_headers[orig_header]) {
                                        $input.val(wpdatatable_config.columns_by_headers[orig_header].display_header)
                                            .prop('disabled', true);
                                    } else {
                                        $input.val(val)
                                            .prop('disabled', true);
                                    }
                                }

                            }
                        });
                    });
                }
            },

            setShowCartInformation: function (showCartInformation) {
                wpdatatable_config.showCartInformation = showCartInformation;
                jQuery('#wdt-shot-woo-cart-information').prop('checked', showCartInformation);
            }

        });

        /**
         * Change regular WP Posts Query parameters
         */
        $('.wdt-wp-query-parameter').on('change', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            wpdatatable_config.queryParameters[this.dataset.value] = $(this).hasClass("toggle-switch") ? $(this).find('input').is(":checked") : $(this).val();
        });

        $(document).on('input change', '.wdt_wp_query_tax_parameter', handleParameterChange('tax_query', 'tax'));
        $(document).on('input change', '.wdt_wp_query_meta_parameter', handleParameterChange('meta_query', 'meta'));
        $(document).on('input change', '.wdt_wp_query_date_parameter', handleParameterChange('date_query', 'date'));

        /**
         * Change Meta, Taxonomy or Date parameters
         */
        function handleParameterChange(parameterType, queryType) {
            return function () {
                let value = $(this).hasClass('wdt-checkbox-parameter') ? $(this).is(':checked') : $(this).val();
                let idPrefix = 'wdt-wp-query-' + queryType + '-';
                if (!this.id.startsWith(idPrefix + 'relation')) {
                    if (!wpdatatable_config.queryParameters[parameterType]) {
                        wpdatatable_config.queryParameters[parameterType] = {};
                    }
                    let numberOfExisting = window[parameterType + '_counter']

                    let id = this.dataset.count < numberOfExisting ? this.dataset.count : this.dataset.count - 1;

                    if (!wpdatatable_config.queryParameters[parameterType][id]) {
                        wpdatatable_config.queryParameters[parameterType][id] = {};
                    }
                    wpdatatable_config.queryParameters[parameterType][id][this.dataset.value] = value;
                } else {
                    wpdatatable_config.queryParameters[parameterType]['relation'] = value;
                }
            };
        }

        /**
         * Change the Custom Field Column parameters
         */
        $(document).on('input change', '.wdt_wp_query_cf_parameter', function () {
            let value = $(this).val();
            let key = this.dataset.value;
            let count = this.dataset.count;

            if (!wpdatatable_config.queryParameters.customFieldColumns[count]) {
                wpdatatable_config.queryParameters.customFieldColumns[count] = {};
            }

            wpdatatable_config.queryParameters.customFieldColumns[count][key] = value;
        });

        /**
         * Change WooCommerce table parameters
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
                wpdatatable_config.queryParameters[wooParameter] = inputValue;
            }
        });

        function handleTaxParameter(inputValue, wooParameter) {
            if (!inputValue || inputValue.length === 0) {
                // Clear the tax query if no input is selected
                delete wpdatatable_config.queryParameters.tax_query[wooParameter];
                return;
            }

            if (wooParameter === 'product_visibility') {
                handleProductVisibility(inputValue, wooParameter);
            } else {
                wpdatatable_config.queryParameters.tax_query[wooParameter] = {
                    taxonomy: wooParameter, field: 'slug', terms: inputValue
                };
            }
        }


        function handleMetaParameter(inputValue, wooParameter) {
            if (inputValue.length === 0 || inputValue === '') {
                delete wpdatatable_config.queryParameters.meta_query[wooParameter];
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

                wpdatatable_config.queryParameters.meta_query[wooParameter] = {
                    key: wooParameter, value: regexpPattern, compare: 'REGEXP', type: 'CHAR'
                };
            } else if (inputValue.includes(',')) {
                // Handle multiple comma-separated values for other cases
                const ids = inputValue.split(',').map(id => id.trim());
                wpdatatable_config.queryParameters['meta_query'][wooParameter] = {
                    key: wooParameter, value: ids, compare: compare, type: type
                };
            } else {
                wpdatatable_config.queryParameters.meta_query[wooParameter] = {
                    key: wooParameter, value: inputValue, compare: compare, type: type
                };
            }
        }

        function handlePriceParameter(inputValue, wooParameter) {
            if (inputValue.length === 0 || inputValue === '') {
                delete wpdatatable_config.queryParameters.meta_query[wooParameter];
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
                    wpdatatable_config.queryParameters.meta_query[priceParameter] = {
                        key: priceParameter,
                        value: [parseFloat(minPrice), parseFloat(maxPrice)],
                        compare: 'BETWEEN',
                        type: 'NUMERIC'
                    };
                } else {
                    delete wpdatatable_config.queryParameters.meta_query[priceParameter];
                }
            } else {
                const price = $(`input[data-value="${priceParameter}"]`).val();
                wpdatatable_config.queryParameters.meta_query[priceParameter] = {
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
                    wpdatatable_config.queryParameters.meta_query[wooParameter] = {
                        key: wooParameter,
                        value: [parseFloat(minPrice), parseFloat(maxPrice)],
                        compare: 'BETWEEN',
                        type: 'NUMERIC'
                    };
                } else {
                    delete wpdatatable_config.queryParameters.meta_query[wooParameter];
                }
            } else {
                wpdatatable_config.queryParameters.meta_query[wooParameter] = {
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
                delete wpdatatable_config.queryParameters.tax_query[wooParameter];
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
                        taxonomy: wooParameter, field: 'name', terms: [value], operator: 'IN'
                    };
                }
            });

            // Apply "OR" relation for multiple selected options
            if (taxQueryConditions.length > 1) {
                wpdatatable_config.queryParameters.tax_query = {
                    relation: 'OR', ...taxQueryConditions,
                };
            } else {
                wpdatatable_config.queryParameters.tax_query[wooParameter] = taxQueryConditions[0];
            }
        }
    });

})(jQuery);
