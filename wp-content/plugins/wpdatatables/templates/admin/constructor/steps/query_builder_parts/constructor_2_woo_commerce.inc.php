<div class="card-body card-padding">
    <div role="tabpanel" class="tab-pane active main-wdt-woo-settings"
         id="main-wdt-woo-settings">
        <ul class="tab-nav wdt-main-menu main-wdt-woo-settings-ul" role="tablist">
            <li class="active general-settings-tab">
                <a href="#general-settings-tab" aria-controls="general-settings-tab" role="tab"
                   data-toggle="tab"><?php esc_html_e('General', 'wpdatatables'); ?></a>
            </li>
            <li class="product-settings-tab">
                <a href="#product-settings-tab" aria-controls="product-settings-tab" role="tab"
                   data-toggle="tab"><?php esc_html_e('Product', 'wpdatatables'); ?></a>
            </li>
            <li class="linked-products-settings-tab">
                <a href="#linked-products-settings-tab" aria-controls="linked-products-settings-tab" role="tab"
                   data-toggle="tab"><?php esc_html_e('Linked Products', 'wpdatatables'); ?></a>
            </li>
            <li class="prices-settings-tab">
                <a href="#prices-settings-tab" aria-controls="prices-settings-tab"
                   role="tab" data-toggle="tab"><?php esc_html_e('Prices', 'wpdatatables'); ?></a>
            </li>
            <li class="measurement-settings-tab">
                <a href="#measurement-settings-tab" aria-controls="measurement-settings-tab" role="tab"
                   data-toggle="tab"><?php esc_html_e('Measurements', 'wpdatatables'); ?></a>
            </li>
            <li class="woo-cf-settings-tab">
                <a href="#woo-cf-settings-tab" aria-controls="woo-cf-settings-tab" role="tab"
                   data-toggle="tab"><?php esc_html_e('Custom Fields', 'wpdatatables'); ?></a>
            </li>
        </ul>
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="general-settings-tab">
                <div class="row">
                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Product status', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Choose post status. Leaving this empty will retrieve all post statuses.', 'wpdatatables'); ?>"></i>
                        </h4>

                        <div class="select">
                            <select class="form-control selectpicker wdt-woo-parameter" multiple="multiple"
                                    title="<?php esc_attr_e('All', 'wpdatatables'); ?>"
                                    id="wdt-woo-commerce-post-statuses" data-value="post_status">
                                <option value="publish"><?php esc_html_e('Published', 'wpdatatables'); ?></option>
                                <option value="pending"><?php esc_html_e('Pending', 'wpdatatables'); ?></option>
                                <option value="draft"><?php esc_html_e('Draft', 'wpdatatables'); ?></option>
                                <option value="auto-draft"><?php esc_html_e('Auto-draft', 'wpdatatables'); ?></option>
                                <option value="future"><?php esc_html_e('Future', 'wpdatatables'); ?></option>
                                <option value="private"><?php esc_html_e('Private', 'wpdatatables'); ?></option>
                                <option value="inherit"><?php esc_html_e('Revision (Inherit)', 'wpdatatables'); ?></option>
                                <option value="trash"><?php esc_html_e('Trash', 'wpdatatables'); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Product Type', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Select product types. Leaving this blank will retrieve all types.', 'wpdatatables'); ?>"></i>
                        </h4>

                        <div class="select">
                            <select class="form-control selectpicker wdt-woo-parameter" multiple="multiple"
                                    title="<?php esc_attr_e('All types', 'wpdatatables'); ?>"
                                    id="wdt-woo-commerce-types" data-value="product_type">
                                <?php $productTypes = array('simple', 'variable', 'grouped', 'external');
                                foreach ($productTypes as $type) : ?>
                                    <option value="<?php echo $type; ?>">
                                        <?php echo ucfirst($type); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Include', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Include', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="fg-line form-group m-b-0">
                            <input id="wdt-woo-commerce-product-in" type="text"
                                   class="form-control input-sm wdt-woo-parameter" data-value="post__in"
                                   placeholder="<?php esc_attr_e('Include', 'wpdatatables'); ?>">
                        </div>
                    </div>

                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Exclude', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Exclude', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="fg-line form-group m-b-0">
                            <input id="wdt-woo-commerce-exclude" type="text"
                                   class="form-control input-sm wdt-woo-parameter" data-value="post__not_in"
                                   placeholder="<?php esc_attr_e('Exclude', 'wpdatatables'); ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Order by', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Order by', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="fg-line form-group m-b-0">
                            <input id="wdt-woo-commerce-order-by" type="text"
                                   class="form-control input-sm wdt-woo-parameter" data-value="orderby"
                                   placeholder="<?php esc_attr_e('Order by', 'wpdatatables'); ?>">
                        </div>
                    </div>

                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Order', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Order', 'wpdatatables'); ?>"></i>
                        </h4>

                        <div class="select">
                            <select class="form-control selectpicker wdt-woo-parameter"
                                    title="<?php esc_attr_e('Order', 'wpdatatables'); ?>"
                                    id="wdt-woo-commerce-order" data-value="order">
                                <option value="ASC"><?php esc_html_e('Ascending', 'wpdatatables'); ?></option>
                                <option value="DESC"><?php esc_html_e('Descending', 'wpdatatables'); ?></option>
                            </select>

                        </div>
                    </div>

                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Author ID', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Author ID', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="form-group">
                            <div class="fg-line wdt-custom-number-input">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                                        data-type="minus" data-field="wdt-woo-author-id">
                                    <i class="wpdt-icon-minus"></i>
                                </button>
                                <input type="number" name="wdt-woo-author-id" min="0"
                                       class="form-control input-sm input-number wdt-woo-parameter"
                                       id="wdt-woo-author-id"
                                       data-value="author"
                                       placeholder="<?php esc_attr_e('Author ID', 'wpdatatables'); ?>">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                                        data-type="plus" data-field="wdt-woo-author-id">
                                    <i class="wpdt-icon-plus-full"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div role="tabpanel" class="tab-pane fade" id="product-settings-tab">
                <div class="row">

                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Product Tag', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Select product tags. Leaving this blank will retrieve all tags.', 'wpdatatables'); ?>"></i>
                        </h4>

                        <div class="select">
                            <select class="form-control selectpicker wdt-woo-parameter" multiple="multiple"
                                    title="<?php esc_attr_e('All tags', 'wpdatatables'); ?>"
                                    id="wdt-woo-commerce-tags" data-value="product_tag">
                                <?php
                                $tags = get_terms(array(
                                    'taxonomy' => 'product_tag',
                                    'hide_empty' => false,
                                ));

                                foreach ($tags as $tag) : ?>
                                    <option value="<?php echo esc_attr($tag->slug); ?>">
                                        <?php echo esc_html($tag->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Product Category', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Select product categories. Leaving this blank will retrieve all categories.', 'wpdatatables'); ?>"></i>
                        </h4>

                        <div class="select">
                            <select class="form-control selectpicker wdt-woo-parameter" multiple="multiple"
                                    title="<?php esc_attr_e('All categories', 'wpdatatables'); ?>"
                                    id="wdt-woo-commerce-categories" data-value="product_cat">
                                <?php
                                $categories = get_terms(array(
                                    'taxonomy' => 'product_cat',
                                    'hide_empty' => false,
                                ));
                                foreach ($categories as $category) : ?>
                                    <option value="<?php echo esc_attr($category->slug); ?>">
                                        <?php echo esc_html($category->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('SKU', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('SKU', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="fg-line form-group m-b-0">
                            <input id="wdt-woo-commerce-sku" type="text"
                                   class="form-control input-sm wdt-woo-parameter" data-value="_sku"
                                   placeholder="<?php esc_attr_e('SKU', 'wpdatatables'); ?>">
                        </div>
                    </div>

                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Total sales', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Total sales', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="form-group">
                            <div class="fg-line wdt-custom-number-input">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                                        data-type="minus" data-field="wdt-woo-total-sales">
                                    <i class="wpdt-icon-minus"></i>
                                </button>
                                <input type="number" name="wdt-woo-total-sales" min="0"
                                       class="form-control input-sm input-number wdt-woo-parameter"
                                       id="wdt-woo-total-sales"
                                       data-value="total_sales"
                                       placeholder="<?php esc_attr_e('Total sales', 'wpdatatables'); ?>">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                                        data-type="plus" data-field="wdt-woo-total-sales">
                                    <i class="wpdt-icon-plus-full"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Stock quantity', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Stock quantity', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="form-group">
                            <div class="fg-line wdt-custom-number-input">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                                        data-type="minus" data-field="wdt-woo-stock-quantity">
                                    <i class="wpdt-icon-minus"></i>
                                </button>
                                <input type="number" name="wdt-woo-stock-quantity" min="0"
                                       class="form-control input-sm input-number wdt-woo-parameter"
                                       id="wdt-woo-stock-quantity"
                                       data-value="_stock"
                                       placeholder="<?php esc_attr_e('Stock quantity', 'wpdatatables'); ?>">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                                        data-type="plus" data-field="wdt-woo-stock-quantity">
                                    <i class="wpdt-icon-plus-full"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Stock status', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Stock status', 'wpdatatables'); ?>"></i>
                        </h4>

                        <div class="select">
                            <select class="form-control selectpicker wdt-woo-parameter" multiple="multiple"
                                    title="<?php esc_attr_e('Any', 'wpdatatables'); ?>"
                                    id="wdt-woo-commerce-stock-status" data-value="_stock_status">
                                <option value="instock"><?php esc_html_e('In stock', 'wpdatatables'); ?></option>
                                <option value="outofstock"><?php esc_html_e('Out of stock', 'wpdatatables'); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Back orders', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Back orders', 'wpdatatables'); ?>"></i>
                        </h4>

                        <div class="select">
                            <select class="form-control selectpicker wdt-woo-parameter" multiple="multiple"
                                    title="<?php esc_attr_e('Back orders', 'wpdatatables'); ?>"
                                    id="wdt-woo-commerce-back-orders" data-value="_backorders">
                                <option value="yes"><?php esc_html_e('Yes', 'wpdatatables'); ?></option>
                                <option value="no"><?php esc_html_e('No', 'wpdatatables'); ?></option>
                                <option value="notify"><?php esc_html_e('Notify', 'wpdatatables'); ?></option>
                            </select>

                        </div>
                    </div>

                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Visibility', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Visibility', 'wpdatatables'); ?>"></i>
                        </h4>

                        <div class="select">
                            <select class="form-control selectpicker wdt-woo-parameter" multiple="multiple"
                                    title="<?php esc_attr_e('Visibility', 'wpdatatables'); ?>"
                                    id="wdt-woo-commerce-visibility" data-value="product_visibility">
                                <option value=""><?php esc_html_e('Visible', 'wpdatatables'); ?></option>
                                <option value="exclude-from-search"><?php esc_html_e('Catalog', 'wpdatatables'); ?></option>
                                <option value="exclude-from-catalog"><?php esc_html_e('Search', 'wpdatatables'); ?></option>
                                <option value="exclude-from-catalog exclude-from-search"><?php esc_html_e('Hidden', 'wpdatatables'); ?></option>
                                <option value="featured"><?php esc_html_e('Featured', 'wpdatatables'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Average rating', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Average rating', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="form-group">
                            <div class="fg-line wdt-custom-number-input">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                                        data-type="minus" data-field="wdt-woo-avg-rating">
                                    <i class="wpdt-icon-minus"></i>
                                </button>
                                <input type="number" name="wdt-woo-avg-rating" min="0"
                                       class="form-control input-sm input-number wdt-woo-parameter"
                                       id="wdt-woo-avg-rating"
                                       data-value="_wc_average_rating"
                                       placeholder="<?php esc_attr_e('Average rating', 'wpdatatables'); ?>">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                                        data-type="plus" data-field="wdt-woo-avg-rating">
                                    <i class="wpdt-icon-plus-full"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Review count', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Review count', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="form-group">
                            <div class="fg-line wdt-custom-number-input">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                                        data-type="minus" data-field="wdt-woo-review-count">
                                    <i class="wpdt-icon-minus"></i>
                                </button>
                                <input type="number" name="wdt-woo-review-count" min="0"
                                       class="form-control input-sm input-number wdt-woo-parameter"
                                       id="wdt-woo-review-count"
                                       data-value="_wc_review_count"
                                       placeholder="<?php esc_attr_e('Review count', 'wpdatatables'); ?>">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                                        data-type="plus" data-field="wdt-woo-review-count">
                                    <i class="wpdt-icon-plus-full"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div role="tabpanel" class="tab-pane fade" id="linked-products-settings-tab">
                <div class="row">
                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Up-sells', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Up-sells', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="fg-line form-group m-b-0">
                            <input id="wdt-woo-upsells" type="text"
                                   class="form-control input-sm wdt-woo-parameter" data-value="_upsell_ids"
                                   placeholder="<?php esc_attr_e('Up-sells', 'wpdatatables'); ?>">
                        </div>
                    </div>

                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Cross-sells', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Cross-sells', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="fg-line form-group m-b-0">
                            <input id="wdt-woo-downsells" type="text"
                                   class="form-control input-sm wdt-woo-parameter" data-value="_crosssell_ids"
                                   placeholder="<?php esc_attr_e('Cross-sells', 'wpdatatables'); ?>">
                        </div>
                    </div>

                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Parent ID', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Parent ID', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="form-group">
                            <div class="fg-line wdt-custom-number-input">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                                        data-type="minus" data-field="wdt-woo-parent-id">
                                    <i class="wpdt-icon-minus"></i>
                                </button>
                                <input type="number" name="wdt-woo-parent-id" min="0"
                                       class="form-control input-sm input-number wdt-woo-parameter"
                                       id="wdt-woo-parent-id"
                                       data-value="post_parent"
                                       placeholder="<?php esc_attr_e('Parent ID', 'wpdatatables'); ?>">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                                        data-type="plus" data-field="wdt-woo-parent-id">
                                    <i class="wpdt-icon-plus-full"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div role="tabpanel" class="tab-pane fade" id="prices-settings-tab">
                <div class="row">
                    <div class="col-sm-4 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Price', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Price', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="select">
                            <select class="form-control selectpicker form-control input-sm wdt-woo-parameter price-comparison-operator"
                                    data-target="_price" data-value="_price_operator">
                                <option value="="><?php esc_html_e('Equals (=)', 'wpdatatables'); ?></option>
                                <option value=">="><?php esc_html_e('Greater Than or Equals (>=)', 'wpdatatables'); ?></option>
                                <option value=">"><?php esc_html_e('Greater Than (>)', 'wpdatatables'); ?></option>
                                <option value="<"><?php esc_html_e('Less Than (<)', 'wpdatatables'); ?></option>
                                <option value="<="><?php esc_html_e('Less Than or Equals (<=)', 'wpdatatables'); ?></option>
                                <option value="between"><?php esc_html_e('Between', 'wpdatatables'); ?></option>
                            </select>
                            <div class="fg-line wdt-custom-number-input m-t-10" data-input="_price">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                                        data-type="minus" data-field="wdt-woo-price">
                                    <i class="wpdt-icon-minus"></i>
                                </button>
                                <input type="number" name="wdt-woo-price" min="0" step="0.01"
                                       class="form-control input-sm input-number wdt-woo-parameter"
                                       id="wdt-woo-price"
                                       data-value="_price"
                                       placeholder="<?php esc_attr_e('Price', 'wpdatatables'); ?>">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                                        data-type="plus" data-field="wdt-woo-price">
                                    <i class="wpdt-icon-plus-full"></i>
                                </button>
                            </div>
                            <div class="wdt-woo-price-range-inputs wdt-custom-number-input" data-parent="_price"
                                 style="display: none;">
                                <div class="form-group">
                                    <div class="fg-line wdt-custom-number-input m-t-10">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                                                data-type="minus" data-field="wdt_woo_price_min">
                                            <i class="wpdt-icon-minus"></i>
                                        </button>
                                        <input type="number"
                                               class="form-control input-sm price-range-min wdt-woo-parameter"
                                               data-value="_price"
                                               id="wdt_woo_price_min"
                                               placeholder="<?php esc_attr_e('Min Price', 'wpdatatables'); ?>">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                                                data-type="plus" data-field="wdt_woo_price_min">
                                            <i class="wpdt-icon-plus-full"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="fg-line wdt-custom-number-input m-t-10">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                                                data-type="minus" data-field="wdt_woo_price_max">
                                            <i class="wpdt-icon-minus"></i>
                                        </button>
                                        <input type="number"
                                               class="form-control input-sm price-range-max wdt-woo-parameter"
                                               data-value="_price"
                                               id="wdt_woo_price_max"
                                               placeholder="<?php esc_attr_e('Max Price', 'wpdatatables'); ?>">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                                                data-type="plus" data-field="wdt_woo_price_max">
                                            <i class="wpdt-icon-plus-full"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-4 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Regular Price', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Regular Price', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="select">
                            <select class="form-control selectpicker form-control input-sm wdt-woo-parameter price-comparison-operator"
                                    data-target="_regular_price" data-value="_regular_price_operator">
                                <option value="="><?php esc_html_e('Equals (=)', 'wpdatatables'); ?></option>
                                <option value=">="><?php esc_html_e('Greater Than or Equals (>=)', 'wpdatatables'); ?></option>
                                <option value=">"><?php esc_html_e('Greater Than (>)', 'wpdatatables'); ?></option>
                                <option value="<"><?php esc_html_e('Less Than (<)', 'wpdatatables'); ?></option>
                                <option value="<="><?php esc_html_e('Less Than or Equals (<=)', 'wpdatatables'); ?></option>
                                <option value="between"><?php esc_html_e('Between', 'wpdatatables'); ?></option>
                            </select>
                            <div class="fg-line wdt-custom-number-input m-t-10" data-input="_regular_price">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                                        data-type="minus" data-field="wdt-woo-regular-price">
                                    <i class="wpdt-icon-minus"></i>
                                </button>
                                <input type="number" name="wdt-woo-regular-price" min="0" step="0.01"
                                       class="form-control input-sm input-number wdt-woo-parameter"
                                       id="wdt-woo-regular-price"
                                       data-value="_regular_price"
                                       placeholder="<?php esc_attr_e('Regular Price', 'wpdatatables'); ?>">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                                        data-type="plus" data-field="wdt-woo-regular-price">
                                    <i class="wpdt-icon-plus-full"></i>
                                </button>
                            </div>
                            <div class="wdt-woo-price-range-inputs wdt-custom-number-input" data-parent="_regular_price"
                                 style="display: none;">
                                <div class="form-group">
                                    <div class="fg-line wdt-custom-number-input m-t-10">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                                                data-type="minus" data-field="wdt_woo_regular_price_min">
                                            <i class="wpdt-icon-minus"></i>
                                        </button>
                                        <input type="number"
                                               class="form-control input-sm price-range-min wdt-woo-parameter"
                                               data-value="_regular_price"
                                               id="wdt_woo_regular_price_min"
                                               placeholder="<?php esc_attr_e('Min Regular Price', 'wpdatatables'); ?>">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                                                data-type="plus" data-field="wdt_woo_regular_price_min">
                                            <i class="wpdt-icon-plus-full"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="fg-line wdt-custom-number-input m-t-10">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                                                data-type="minus" data-field="wdt_woo_regular_price_max">
                                            <i class="wpdt-icon-minus"></i>
                                        </button>
                                        <input type="number"
                                               class="form-control input-sm price-range-max wdt-woo-parameter"
                                               data-value="_regular_price"
                                               id="wdt_woo_regular_price_max"
                                               placeholder="<?php esc_attr_e('Max Regular Price', 'wpdatatables'); ?>">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                                                data-type="plus" data-field="wdt_woo_regular_price_max">
                                            <i class="wpdt-icon-plus-full"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-4 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Sale Price', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Sale Price', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="select">
                            <select class="form-control selectpicker form-control input-sm wdt-woo-parameter price-comparison-operator"
                                    data-target="_sale_price" data-value="_sale_price_operator">
                                <option value="="><?php esc_html_e('Equals (=)', 'wpdatatables'); ?></option>
                                <option value=">="><?php esc_html_e('Greater Than or Equals (>=)', 'wpdatatables'); ?></option>
                                <option value=">"><?php esc_html_e('Greater Than (>)', 'wpdatatables'); ?></option>
                                <option value="<"><?php esc_html_e('Less Than (<)', 'wpdatatables'); ?></option>
                                <option value="<="><?php esc_html_e('Less Than or Equals (<=)', 'wpdatatables'); ?></option>
                                <option value="between"><?php esc_html_e('Between', 'wpdatatables'); ?></option>
                            </select>
                            <div class="fg-line wdt-custom-number-input m-t-10" data-input="_sale_price">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                                        data-type="minus" data-field="wdt-woo-sale-price">
                                    <i class="wpdt-icon-minus"></i>
                                </button>
                                <input type="number" name="wdt-woo-sale-price" min="0" step="0.01"
                                       class="form-control input-sm input-number wdt-woo-parameter"
                                       id="wdt-woo-sale-price"
                                       data-value="_sale_price"
                                       placeholder="<?php esc_attr_e('Sale Price', 'wpdatatables'); ?>">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                                        data-type="plus" data-field="wdt-woo-sale-price">
                                    <i class="wpdt-icon-plus-full"></i>
                                </button>
                            </div>
                            <div class="wdt-woo-price-range-inputs wdt-custom-number-input" data-parent="_sale_price"
                                 style="display: none;">
                                <div class="form-group">
                                    <div class="fg-line wdt-custom-number-input m-t-10">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                                                data-type="minus" data-field="wdt_woo_sale_price_min">
                                            <i class="wpdt-icon-minus"></i>
                                        </button>
                                        <input type="number"
                                               class="form-control input-sm price-range-min wdt-woo-parameter"
                                               data-value="_sale_price"
                                               id="wdt_woo_sale_price_min"
                                               placeholder="<?php esc_attr_e('Min Sale Price', 'wpdatatables'); ?>">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                                                data-type="plus" data-field="wdt_woo_sale_price_min">
                                            <i class="wpdt-icon-plus-full"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="fg-line wdt-custom-number-input m-t-10">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                                                data-type="minus" data-field="wdt_woo_sale_price_max">
                                            <i class="wpdt-icon-minus"></i>
                                        </button>
                                        <input type="number"
                                               class="form-control input-sm price-range-max wdt-woo-parameter"
                                               data-value="_sale_price"
                                               id="wdt_woo_sale_price_max"
                                               placeholder="<?php esc_attr_e('Max Sale Price', 'wpdatatables'); ?>">
                                        <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                                                data-type="plus" data-field="wdt_woo_sale_price_max">
                                            <i class="wpdt-icon-plus-full"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div role="tabpanel" class="tab-pane fade" id="measurement-settings-tab">
                <div class="row">
                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Width', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Width', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="form-group">
                            <div class="fg-line wdt-custom-number-input">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                                        data-type="minus" data-field="wdt-woo-width">
                                    <i class="wpdt-icon-minus"></i>
                                </button>
                                <input type="number" name="wdt-woo-width" min="0" step="0.01"
                                       class="form-control input-sm input-number wdt-woo-parameter"
                                       id="wdt-woo-width"
                                       data-value="_width"
                                       placeholder="<?php esc_attr_e('Width', 'wpdatatables'); ?>">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                                        data-type="plus" data-field="wdt-woo-width">
                                    <i class="wpdt-icon-plus-full"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Length', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Length', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="form-group">
                            <div class="fg-line wdt-custom-number-input">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                                        data-type="minus" data-field="wdt-woo-length">
                                    <i class="wpdt-icon-minus"></i>
                                </button>
                                <input type="number" name="wdt-woo-length" min="0" step="0.01"
                                       class="form-control input-sm input-number wdt-woo-parameter"
                                       id="wdt-woo-length"
                                       data-value="_length"
                                       placeholder="<?php esc_attr_e('Length', 'wpdatatables'); ?>">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                                        data-type="plus" data-field="wdt-woo-length">
                                    <i class="wpdt-icon-plus-full"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Height', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Height', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="form-group">
                            <div class="fg-line wdt-custom-number-input">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                                        data-type="minus" data-field="wdt-woo-height">
                                    <i class="wpdt-icon-minus"></i>
                                </button>
                                <input type="number" name="wdt-woo-height" min="0" step="0.01"
                                       class="form-control input-sm input-number wdt-woo-parameter"
                                       id="wdt-woo-height"
                                       data-value="_height"
                                       placeholder="<?php esc_attr_e('Height', 'wpdatatables'); ?>">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                                        data-type="plus" data-field="wdt-woo-height">
                                    <i class="wpdt-icon-plus-full"></i>
                                </button>
                            </div>
                        </div>
                    </div>


                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Weight', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Weight', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="form-group">
                            <div class="fg-line wdt-custom-number-input">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                                        data-type="minus" data-field="wdt-woo-weight">
                                    <i class="wpdt-icon-minus"></i>
                                </button>
                                <input type="number" name="wdt-woo-weight" min="0" step="0.01"
                                       class="form-control input-sm input-number wdt-woo-parameter"
                                       id="wdt-woo-weight"
                                       data-value="_weight"
                                       placeholder="<?php esc_attr_e('Weight', 'wpdatatables'); ?>">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                                        data-type="plus" data-field="wdt-woo-weight">
                                    <i class="wpdt-icon-plus-full"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="m-b-16 wdt-coming-soon-notice">
                        <h4 class="f-14">
                            <i class="wpdt-icon-star-full m-r-5" style="color: #091D70;"></i>
                            <?php esc_html_e('Coming soon', 'wpdatatables'); ?></h4>
                        <p class="m-b-0"><?php esc_html_e('Currently, measurement parameters in the table use the EQUALS operator (=), which means only products with the exact measurement values you specify will be shown. Soon, you\'ll have the flexibility to choose different operators (like greater than, less than, etc.) for each parameter, giving you much more control over how products are filtered. Stay tuned for this powerful new feature!', 'wpdatatables'); ?></p>
                    </div>
                </div>
            </div>

            <div role="tabpanel" class="tab-pane fade" id="woo-cf-settings-tab">
                <div class="row">
                    <div id="wdt-woo-commerce-cf-container">
                    </div>

                    <div class="col-sm-12 p-l-0 m-t-10">
                        <button class="btn pull-left wdt-woo-commerce-add-cf-column">
                            <i class="wpdt-icon-plus-thin"></i> <?php esc_html_e('Add New Custom Field Column', 'wpdatatables'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
if (defined('WDT_WOO_COMMERCE_PATH')) {
    include WDT_WOO_COMMERCE_PATH . 'source/templates/woo_custom_field_columns_block.inc.php';
}
?>

