(function ($) {
    $(function () {
        // Add event listener for the "Add to Cart" button above the table
        $('body').on('click', '.wdt-add-to-cart-button', function (e) {
            e.preventDefault();

            let tableId = Number(this.dataset.value);
            let tableSelector = $('table.wpDataTable[data-wpdatatable_id=' + tableId + ']').get(0).id;
            let selectedRows = wpDataTables[tableSelector].DataTable().rows({selected: true}).data();
            let selectedIndexes = wpDataTables[tableSelector].DataTable().rows({selected: true}).indexes();
            let productColumnIndex = getColumnIndexByHeader('product_id', tableSelector)
            let productIds = [];
            selectedRows.each(function (value) {
                let cleanedValue = value[productColumnIndex].replace(/[^\d]/g, '');
                productIds.push(parseInt(cleanedValue, 10));
            });


            if (productIds.length > 0) {
                $.ajax({
                    url: wdt_ajax_object.ajaxurl,
                    type: 'POST',
                    data: {
                        wdtNonce: $('#wdtNonce').val(),
                        action: 'wpdatatables_add_multiple_products_to_cart',
                        productIds: productIds
                    },
                    success: function (data) {
                        if (data.success) {
                            updateCartInfo();

                            // Update the specific row with a mini cart and product quantity
                            selectedRows.each(function (value, index) {
                                let productIdColIndex = getColumnIndexByHeader('product_id', tableSelector)
                                let product_id = value[productIdColIndex];
                                let cartHtml = `
                                    <div class="wdt-woo-mini-cart" name="wdt-woo-mini-cart">
                                        <a href="${data.data.cart_url}" class="wdt-woo-mini-cart-icon">
                                            <i class="wpdt-icon-cart"></i>
                                            <span class="wdt-woo-cart-quantity">` + data.data.product_quantities[product_id] + `</span>
                                        </a>
                                    </div>
                                `;

                                let table = wpDataTables[tableSelector].DataTable();
                                let columnIndex = getColumnIndexByHeader('add_to_cart_button', tableSelector);
                                let rowId = wpDataTables[tableSelector].DataTable().rows({selected: true}).indexes()[index];
                                let currentContent = table.cell(rowId, columnIndex).node();
                                if (!currentContent.children.namedItem('wdt-woo-mini-cart')) {
                                    $(currentContent).append(cartHtml);
                                } else {
                                    $(currentContent).find('.wdt-woo-mini-cart').find('span.wdt-woo-cart-quantity').text(data.data.product_quantities[product_id])
                                }
                            });
                        } else {
                            wdtNotify(
                                wpdatatables_frontend_strings.error_wpdatatables,
                                wpdatatables_frontend_strings.error_adding_to_cart_wpdatatables,
                                'danger'
                            )
                        }
                    },
                    error: function () {
                        wdtNotify(
                            wpdatatables_frontend_strings.error_wpdatatables,
                            wpdatatables_frontend_strings.error_adding_to_cart_wpdatatables,
                            'danger'
                        )
                    }
                });
            } else {
                wdtNotify(
                    wpdatatables_frontend_strings.error_wpdatatables,
                    wpdatatables_frontend_strings.select_products_for_cart_wpdatatables,
                    'danger'
                )
            }
        });

        // Update cart information after adding products to cart
        function updateCartInfo() {
            $.ajax({
                url: wdt_ajax_object.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpdatatables_get_cart_info',
                },
                success: function (data) {
                    if (data.success) {
                        // Update cart total and item count
                        $('.cart-count').html(data.data.item_count);
                        $('.cart-total').html(data.data.total_sum);
                        if (!$('.cart-info').is(':visible') && data.data.total_sum) {
                            $('.cart-info').show();
                        }
                    }
                },
                error: function () {
                    wdtNotify(
                        wpdatatables_frontend_strings.error_wpdatatables,
                        wpdatatables_frontend_strings.error_fetching_cart_info_wpdatatables,
                        'danger'
                    )
                }
            });
        }

        // Listen to add to cart events in the add_to_cart_button column
        $('body').on('click', '.single_add_to_cart_button', function (e) {
            e.preventDefault();

            let tableId = Number(this.dataset.value);
            let tableSelector = $('table.wpDataTable[data-wpdatatable_id=' + tableId + ']').get(0).id;
            let button = $(this);
            let productId = button.data('product_id');
            let quantity = button.closest('.wdt-woo-variable-product').find('input[name="quantity"]').val();
            let variations = {};

            // Get selected variations
            button.closest('.wdt-woo-variable-product').find('.wdt-woo-variation-selector').each(function () {
                let attributeName = 'attribute_' + $(this).data('attribute-name');
                let attributeValue = $(this).val();
                variations[attributeName] = attributeValue;
            });

            let rowId = button.closest('tr')[0]._DT_RowIndex;

            $.ajax({
                url: wdt_ajax_object.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpdatatables_add_single_product_to_cart',
                    product_id: productId,
                    quantity: quantity,
                    variations: variations,
                },
                success: function (data) {
                    if (data.success) {
                        if (data.data.notAdded) {
                            wdtNotify(
                                wpdatatables_frontend_strings.error_wpdatatables,
                                wpdatatables_frontend_strings.could_not_add_to_cart_wpdatatables,
                                'danger'
                            )
                            return;
                        }
                        updateCartInfo();

                        // Update the specific row with a mini cart and product quantity
                        let cartHtml = `
                            <div class="wdt-woo-mini-cart" name="wdt-woo-mini-cart">
                                <a href="${data.data.cart_url}" class="wdt-woo-mini-cart-icon">
                                    <i class="wpdt-icon-cart"></i>
                                    <span class="wdt-woo-cart-quantity">${data.data.product_quantities}</span>
                                </a>
                            </div>
                        `;
                        let table = wpDataTables[tableSelector].DataTable();
                        let columnIndex = getColumnIndexByHeader('add_to_cart_button', tableSelector)
                        let currentContent = table.cell(rowId, columnIndex).node();
                        if (!currentContent.children.namedItem('wdt-woo-mini-cart')) {
                            $(currentContent).append(cartHtml);
                        } else {
                            $(currentContent).find('.wdt-woo-mini-cart').find('span.wdt-woo-cart-quantity').text(data.data.product_quantities)
                        }
                    } else {
                        wdtNotify(
                            wpdatatables_frontend_strings.error_wpdatatables,
                            wpdatatables_frontend_strings.error_adding_to_cart_wpdatatables,
                            'danger'
                        )
                    }
                }
            });
        });

        // Handle change in product attributes (dropdowns)
        $('body').on('change', '.wdt-woo-product-attribute', function () {
            let $row = $(this).closest('.wdt-woo-variable-product');
            let allSelected = true;

            // Check if all dropdowns have a selected value
            $row.find('.wdt-woo-product-attribute').each(function () {
                if (!$(this).val()) {
                    allSelected = false;
                }
            });

            // Enable Add to Cart button if all options are selected
            if (allSelected) {
                $row.find('.single_add_to_cart_button').prop('disabled', false);
            } else {
                $row.find('.single_add_to_cart_button').prop('disabled', true);
            }
        });

        $('body').on('change', '.wdt-woo-variation-selector', function () {
            let allSelected = true;
            $(this).closest('.wdt-woo-variable-product').find('.wdt-woo-variation-selector').each(function () {
                if ($(this).val() === '') {
                    allSelected = false;
                }
            });

            let addToCartButton = $(this).closest('.wdt-woo-variable-product').find('.single_add_to_cart_button');
            if (allSelected) {
                addToCartButton.prop('disabled', false);
            } else {
                addToCartButton.prop('disabled', true);
            }
        });

        // Handle quantity input
        $('body').on('change', '.wdt-woo-product-quantity', function () {
            let quantity = $(this).val();
            if (quantity < 1) {
                $(this).val(1);
            }
        });

        // Remove cart information block above the table
        $('body').on('change', '#wdt-shot-woo-cart-information', function (e) {
            wpdatatable_config.setShowCartInformation($(this).is(':checked') ? 1 : 0);
        });

        // If predefined values are available, pre-fill the dropdowns and enable the button if all are selected
        $('.wdt-woo-variable-product').each(function () {
            let allPreselected = true;

            $(this).find('.wdt-woo-variation-selector').each(function () {
                let preselectedValue = $(this).find('option[selected]').val();
                if (preselectedValue) {
                    $(this).val(preselectedValue);
                } else {
                    allPreselected = false;
                }
            });

            let addToCartButton = $(this).find('.single_add_to_cart_button');
            if (allPreselected) {
                addToCartButton.prop('disabled', false);
            }
        });

        function getColumnIndexByHeader(originalHeader, selector) {
            let table = wpDataTables[selector].DataTable();

            let columnIndex = -1;
            table.columns().every(function (index) {
                let headerText = this.header().textContent.trim();
                if (headerText === originalHeader) {
                    columnIndex = index;
                    return false;
                }
            });

            return columnIndex;
        }
    });
})(jQuery);
