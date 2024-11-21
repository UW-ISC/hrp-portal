let constructedPostQueryData = {
    comment_count_compare: '=',
    post_type: 'any',
    comment_count: {},
    tax_query: {
        relation : 'AND'
    },
    meta_query: {
        relation : 'AND'
    },
    date_query: {
        relation : 'AND'
    }
};
let tax_query_counter = 0;
let meta_query_counter = 0;
let date_query_counter = 0;
const queryTypeMap = {
    'wdt_wp_query_tax_parameter': 'tax_query',
    'wdt_wp_query_meta_parameter': 'meta_query',
    'wdt_wp_query_date_parameter': 'date_query'
};
const queryCounterMap = {
    'tax_query': tax_query_counter,
    'meta_query': meta_query_counter,
    'date_query': date_query_counter
};

(function ($) {
    let wdtNonce = $('#wdtNonce').val();

    /**
     * Change the WP Posts Query preview on parameter change
     */
    $('.wdt-wp-query-parameter').on('change', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        // Get the changed query parameter depending on the input type
        let inputValue = $(this).hasClass("toggle-switch") ? $(this).find('input').is(":checked") : $(this).val();
        if (this.dataset.value.startsWith('comment_count_')) {
            let property = this.dataset.value.slice(14);
            constructedPostQueryData['comment_count'][property] = inputValue;
        } else {
            constructedPostQueryData[this.dataset.value] = inputValue;
        }

        if ($(this).hasClass('toggle-switch')) {
            $(this).find('input').prop('checked', inputValue);
        } else if ($(this).hasClass('selectpicker')) {
            $(this).val(inputValue).selectpicker('refresh');
        } else {
            $(this).val(inputValue)
        }

        renderQueryPreview();
    })

    function renderQueryPreview() {
        $.ajax({
            url: ajaxurl,
            type: 'post',
            dataType: 'json',
            data: {
                action: 'wpdatatables_generate_live_wp_posts_preview',
                queryData: constructedPostQueryData,
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

    /**
     * Change the Meta, Taxonomy, or Date parameters
     */
    $(document).on('input change', '.wdt_wp_query_tax_parameter, .wdt_wp_query_meta_parameter, .wdt_wp_query_date_parameter', function () {
        // Skip triggering the event handler for empty select elements
        if ($(this).val() === '' && $(this).hasClass('bootstrap-select')) {
            return;
        }
        let value = $(this).hasClass('wdt-checkbox-parameter') ? $(this).is(':checked') : $(this).val();
        let className = $(this).attr('class').split(' ').find(selector => queryTypeMap.hasOwnProperty(selector));
        let queryType = queryTypeMap[className];
        let queryCounter = queryCounterMap[queryType];

        if (this.id !== `wdt-wp-query-${queryType}-relation`) {
            let queryId = this.dataset.count - 1;
            if (!constructedPostQueryData[queryType][queryId]) {
                constructedPostQueryData[queryType][queryId] = {};
            }
            if (queryCounter === 1) {
                constructedPostQueryData[queryType][this.dataset.value] = value;
            } else {
                constructedPostQueryData[queryType][queryId][this.dataset.value] = value;
            }
        } else {
            constructedPostQueryData[queryType]['relation'] = value;
        }

        renderQueryPreview();
    });


    /**
     * Add new Custom Field
     */
    $('button.wdt-wp-query-add-custom-field').on('click', function (e) {
        e.preventDefault();
        meta_query_counter++;

        let metaBlockTemplate = $.templates("#wdt-wp-query-meta-template");
        let metaBlockHtml = metaBlockTemplate.render({metaFieldId: meta_query_counter});
        $(metaBlockHtml).appendTo('div#wdt-wp-query-custom-fields-container').animateFadeIn();

        if (meta_query_counter > 1) {
            $("#wdt-wp-query-meta-relation-container").animateFadeIn();
        }
    });

    /**
     * Remove Custom Field
     */
    $(document).on('click', '#wdt-constructor-delete-custom-field', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        let queryId = $(this).closest('.wdt-wp-query-clause-template').find('.wdt_wp_query_meta_parameter').data('count') - 1;
        let queryType = 'meta_query';

        if (constructedPostQueryData[queryType] && constructedPostQueryData[queryType][queryId]) {
            delete constructedPostQueryData[queryType][queryId];
        }
        if (constructedPostQueryData[queryType]) {
            let updatedEntries = {};
            let index = 0;

            for (let key in constructedPostQueryData[queryType]) {
                if(key != 'relation') {
                    updatedEntries[index] = constructedPostQueryData[queryType][key];
                    index++;
                } else {
                    updatedEntries[key] = constructedPostQueryData[queryType][key];
                }
            }

            constructedPostQueryData[queryType] = updatedEntries;
        }
        meta_query_counter--;
        $(this).closest('.wdt-wp-query-clause-template').remove();

        if (meta_query_counter === 1) {
            $("#wdt-wp-query-meta-relation-container").animateFadeOut();
        }

        delete constructedPostQueryData['meta_query'][meta_query_counter];
        renderQueryPreview();
    });

    /**
     * Add new Taxonomy Clause
     */
    $('button.wdt-wp-query-add-tax-clause').on('click', function (e) {
        e.preventDefault();
        tax_query_counter++;

        let taxBlockTemplate = $.templates("#wdt-wp-query-tax-template");
        let taxBlockHtml = taxBlockTemplate.render({taxClauseId: tax_query_counter});
        $(taxBlockHtml).appendTo('div#wdt-wp-query-tax-clause-container').animateFadeIn();

        if (tax_query_counter > 1) {
            $("#wdt-wp-query-tax-relation-container").animateFadeIn();
        }
    });

    /**
     * Remove Taxonomy Clause
     */
    $(document).on('click', '#wdt-constructor-delete-tax-clause', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        let queryId = $(this).closest('.wdt-wp-query-clause-template').find('.wdt_wp_query_tax_parameter').data('count') - 1;
        let queryType = 'tax_query';

        if (constructedPostQueryData[queryType] && constructedPostQueryData[queryType][queryId]) {
            delete constructedPostQueryData[queryType][queryId];
        }
        if (constructedPostQueryData[queryType]) {
            let updatedEntries = {};
            let index = 0;

            for (let key in constructedPostQueryData[queryType]) {
                if(key != 'relation') {
                    updatedEntries[index] = constructedPostQueryData[queryType][key];
                    index++;
                } else {
                    updatedEntries[key] = constructedPostQueryData[queryType][key];
                }
            }

            constructedPostQueryData[queryType] = updatedEntries;
        }
        tax_query_counter--;
        $(this).closest('.wdt-wp-query-clause-template').remove();

        if (tax_query_counter === 1) {
            $("#wdt-wp-query-tax-relation-container").animateFadeOut();
        }
        delete constructedPostQueryData['tax_query'][tax_query_counter];
        renderQueryPreview();
    });

    /**
     * Add new Date Clause
     */
    $('button.wdt-wp-query-add-date-clause').on('click', function (e) {
        e.preventDefault();
        date_query_counter++;

        let dateBlockTemplate = $.templates("#wdt-wp-query-date-template");
        let dateBlockHtml = dateBlockTemplate.render({dateClauseId: date_query_counter});
        $(dateBlockHtml).appendTo('div#wdt-wp-query-date-clause-container').animateFadeIn();

        if (date_query_counter > 1) {
            $("#wdt-wp-query-date-relation-container").animateFadeIn();
        }
    });

    /**
     * Remove Date Clause
     */
    $(document).on('click', '#wdt-constructor-delete-date-clause', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        let queryId = $(this).closest('.wdt-wp-query-date-template').find('.wdt_wp_query_date_parameter').data('count') - 1;
        let queryType = 'date_query';

        if (constructedPostQueryData[queryType] && constructedPostQueryData[queryType][queryId]) {
            delete constructedPostQueryData[queryType][queryId];
        }
        if (constructedPostQueryData[queryType]) {
            let updatedEntries = {};
            let index = 0;

            for (let key in constructedPostQueryData[queryType]) {
                if(key != 'relation') {
                    updatedEntries[index] = constructedPostQueryData[queryType][key];
                    index++;
                } else {
                    updatedEntries[key] = constructedPostQueryData[queryType][key];
                }
            }

            constructedPostQueryData[queryType] = updatedEntries;
        }
        date_query_counter--;
        $(this).closest('.wdt-wp-query-date-template').remove();

        if (date_query_counter === 1) {
            $("#wdt-wp-query-date-relation-container").animateFadeOut();
        }

        delete constructedPostQueryData['date_query'][date_query_counter];
        renderQueryPreview();
    });

})(jQuery);