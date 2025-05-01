<div class="card-body card-padding">
    <div role="tabpanel" class="tab-pane active main-wp-posts-settings"
         id="main-wp-posts-settings">
        <ul class="tab-nav wdt-main-menu main-wp-posts-settings-ul" role="tablist">
            <li class="active general-settings-tab">
                <a href="#general-settings-tab" aria-controls="general-settings-tab" role="tab"
                   data-toggle="tab"><?php esc_html_e('General', 'wpdatatables'); ?></a>
            </li>
            <li class="meta-query-settings-tab">
                <a href="#meta-query-settings-tab" aria-controls="meta-query-settings-tab" role="tab"
                   data-toggle="tab"><?php esc_html_e('Meta Query', 'wpdatatables'); ?></a>
            </li>
            <li class="tax-query-settings-tab">
                <a href="#tax-query-settings-tab" aria-controls="tax-query-settings-tab" role="tab"
                   data-toggle="tab"><?php esc_html_e('Tax Query', 'wpdatatables'); ?></a>
            </li>
            <li class="date-query-settings-tab">
                <a href="#date-query-settings-tab" aria-controls="date-query-settings-tab"
                   role="tab" data-toggle="tab"><?php esc_html_e('Date Query', 'wpdatatables'); ?></a>
            </li>
            <li class="posts-page-settings-tab">
                <a href="#posts-page-settings-tab" aria-controls="posts-page-settings-tab" role="tab"
                   data-toggle="tab"><?php esc_html_e('Post and Page', 'wpdatatables'); ?></a>
            </li>
            <li class="category-tag-settings-tab">
                <a href="#category-tag-settings-tab" aria-controls="category-tag-settings-tab" role="tab"
                   data-toggle="tab"><?php esc_html_e('Category and Tag', 'wpdatatables'); ?></a>
            </li>
            <li class="comments-settings-tab">
                <a href="#comments-settings-tab" aria-controls="comments-settings-tab" role="tab"
                   data-toggle="tab"><?php esc_html_e('Comments', 'wpdatatables'); ?></a>
            </li>
            <li class="author-settings-tab">
                <a href="#author-settings-tab" aria-controls="author-settings-tab" role="tab"
                   data-toggle="tab"><?php esc_html_e('Author', 'wpdatatables'); ?></a>
            </li>
            <li class="cf-settings-tab">
                <a href="#cf-settings-tab" aria-controls="cf-settings-tab" role="tab"
                   data-toggle="tab"><?php esc_html_e('Custom Fields', 'wpdatatables'); ?></a>
            </li>
        </ul>
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="general-settings-tab">
                <div class="row">
                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Post types', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Choose the types of posts to include, such as posts, pages, or custom post types.', 'wpdatatables'); ?>"></i>
                        </h4>

                        <div class="select">
                            <select class="form-control selectpicker wdt-wp-query-parameter" multiple="multiple"
                                    title="<?php esc_attr_e('All', 'wpdatatables'); ?>"
                                    id="wdt-wp-query-post-types" data-value="post_type">
                                <?php $post_types = get_post_types(array(), 'objects'); ?>
                                <?php foreach ($post_types as $post_type) {
                                    echo '<option value="' . $post_type->name . '">' . ucfirst($post_type->name) . '</option>';
                                } ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Post status', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Select which posts to display based on their status, like published, draft, or pending.', 'wpdatatables'); ?>"></i>
                        </h4>

                        <div class="select">
                            <select class="form-control selectpicker wdt-wp-query-parameter" multiple="multiple"
                                    title="<?php esc_attr_e('All', 'wpdatatables'); ?>"
                                    id="wdt-wp-query-post-statuses" data-value="post_status">
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
                            <?php esc_html_e('Search keyword', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Filter posts by a specific keyword in their title, content, or other fields.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="fg-line form-group m-b-0">
                            <input id="wdt-wp-query-search" type="text" data-value="s"
                                   class="form-control input-sm wdt-wp-query-parameter"
                                   placeholder="<?php esc_attr_e('Search keyword', 'wpdatatables'); ?>">
                        </div>
                    </div>

                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Only posts with a password', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin " data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Display only posts that are password protected.', 'wpdatatables'); ?>"></i>
                        </h4>

                        <div class="toggle-switch wdt-wp-query-parameter" data-ts-color="blue"
                             data-value="has_password">
                            <input id="wdt-wp-query-has-password" type="checkbox">
                            <label for="wdt-wp-query-has-password"
                                   class="ts-label"><?php esc_html_e('Include only posts with a password', 'wpdatatables'); ?></label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Post password', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Specify a password to only show posts protected by this password.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="fg-line form-group m-b-0">
                            <input id="wdt-wp-query-post-password" type="text"
                                   class="form-control input-sm wdt-wp-query-parameter" data-value="post_password"
                                   placeholder="<?php esc_attr_e('Post password', 'wpdatatables'); ?>">
                        </div>
                    </div>

                </div>
            </div>

            <div role="tabpanel" class="tab-pane fade" id="tax-query-settings-tab">
                <div class="row">
                    <div id="wdt-wp-query-tax-clause-container">
                    </div>

                    <div class="col-sm-12 p-l-0 m-t-10">
                        <button class="btn pull-left wdt-wp-query-add-tax-clause">
                            <i class="wpdt-icon-plus-thin"></i> <?php esc_html_e('Add New Clause', 'wpdatatables'); ?>
                        </button>
                    </div>

                    <div id="wdt-wp-query-tax-relation-container" class="col-sm-1 m-b-16 hidden">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Relation', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Relation', 'wpdatatables'); ?>"></i>
                        </h4>

                        <div class="select">
                            <select class="form-control selectpicker wdt_wp_query_tax_parameter"
                                    title="<?php esc_attr_e('AND', 'wpdatatables'); ?>"
                                    id="wdt-wp-query-tax_query-relation" data-value="relation">
                                <option value="AND"
                                        selected="selected"><?php esc_html_e('AND', 'wpdatatables'); ?></option>
                                <option value="OR"><?php esc_html_e('OR', 'wpdatatables'); ?></option>
                            </select>
                        </div>
                    </div>

                </div>
            </div>

            <div role="tabpanel" class="tab-pane fade" id="meta-query-settings-tab">
                <div class="row">
                    <div id="wdt-wp-query-custom-fields-container">
                    </div>

                    <div class="col-sm-12 p-l-0 m-t-10">
                        <button class="btn pull-left wdt-wp-query-add-custom-field">
                            <i class="wpdt-icon-plus-thin"></i> <?php esc_html_e('Add Custom Field', 'wpdatatables'); ?>
                        </button>
                    </div>

                    <div id="wdt-wp-query-meta-relation-container" class="col-sm-1 m-b-16 hidden">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Relation', 'wpdatatables'); ?>
                        </h4>

                        <div class="select">
                            <select class="form-control selectpicker wdt_wp_query_meta_parameter"
                                    title="<?php esc_attr_e('AND', 'wpdatatables'); ?>"
                                    id="wdt-wp-query-meta_query-relation" data-value="relation">
                                <option value="AND"
                                        selected="selected"><?php esc_html_e('AND', 'wpdatatables'); ?></option>
                                <option value="OR"><?php esc_html_e('OR', 'wpdatatables'); ?></option>
                            </select>
                        </div>
                    </div>

                </div>
            </div>

            <div role="tabpanel" class="tab-pane fade" id="date-query-settings-tab">
                <div class="row">
                    <div id="wdt-wp-query-date-clause-container">
                    </div>

                    <div class="col-sm-12 p-l-0 m-t-10">
                        <button class="btn pull-left wdt-wp-query-add-date-clause">
                            <i class="wpdt-icon-plus-thin"></i> <?php esc_html_e('Add New Clause', 'wpdatatables'); ?>
                        </button>
                    </div>

                    <div id="wdt-wp-query-date-relation-container" class="col-sm-1 m-b-16 hidden">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Relation', 'wpdatatables'); ?>
                        </h4>

                        <div class="select">
                            <select class="form-control selectpicker wdt_wp_query_date_parameter"
                                    title="<?php esc_attr_e('AND', 'wpdatatables'); ?>"
                                    id="wdt-wp-query-date_query-relation" data-value="relation">
                                <option value="AND"
                                        selected="selected"><?php esc_html_e('AND', 'wpdatatables'); ?></option>
                                <option value="OR"><?php esc_html_e('OR', 'wpdatatables'); ?></option>
                            </select>
                        </div>
                    </div>

                </div>
            </div>

            <div role="tabpanel" class="tab-pane fade" id="posts-page-settings-tab">
                <div class="row">
                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Post ID', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Show posts by their unique ID.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="form-group">
                            <div class="fg-line wdt-custom-number-input">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                                        data-type="minus" data-field="wdt-post-id">
                                    <i class="wpdt-icon-minus"></i>
                                </button>
                                <input type="number" name="wdt-post-id" min="0"
                                       class="form-control input-sm input-number wdt-wp-query-parameter"
                                       id="wdt-wp-query-post-id"
                                       data-value="p"
                                       placeholder="<?php esc_attr_e('Post ID', 'wpdatatables'); ?>">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                                        data-type="plus" data-field="wdt-post-id">
                                    <i class="wpdt-icon-plus-full"></i>
                                </button>
                            </div>
                        </div>

                    </div>
                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Post slug', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Display posts matching the specified slug.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="fg-line form-group m-b-0" data-value="name">
                            <input id="wdt-wp-query-post-slug" type="text"
                                   class="form-control input-sm wdt-wp-query-parameter"
                                   placeholder="<?php esc_attr_e('Post slug', 'wpdatatables'); ?>">
                        </div>

                    </div>
                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Page ID', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Include only pages with specific IDs.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="form-group">
                            <div class="fg-line wdt-custom-number-input">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                                        data-type="minus" data-field="wdt-page-id">
                                    <i class="wpdt-icon-minus"></i>
                                </button>
                                <input type="number" name="wdt-page-id" min="0"
                                       class="form-control input-sm input-number wdt-wp-query-parameter"
                                       id="wdt-wp-query-page-id"
                                       data-value="page_id"
                                       placeholder="<?php esc_attr_e('Page ID', 'wpdatatables'); ?>">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                                        data-type="plus" data-field="wdt-page-id">
                                    <i class="wpdt-icon-plus-full"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Post parent', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Display child posts of a specific parent post.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="form-group">
                            <div class="fg-line wdt-custom-number-input">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                                        data-type="minus" data-field="wdt-post-parent">
                                    <i class="wpdt-icon-minus"></i>
                                </button>
                                <input type="number" name="wdt-post-parent" min="0"
                                       class="form-control input-sm input-number wdt-wp-query-parameter"
                                       id="wdt-wp-query-post-parent"
                                       data-value="post_parent"
                                       placeholder="<?php esc_attr_e('Post parent', 'wpdatatables'); ?>">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                                        data-type="plus" data-field="wdt-post-parent">
                                    <i class="wpdt-icon-plus-full"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Post parent in', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Display posts with specific parent posts.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="fg-line form-group m-b-0">
                            <input id="wdt-wp-query-post-parent-in" type="text"
                                   class="form-control input-sm wdt-wp-query-parameter"
                                   data-value="post_parent__in"
                                   placeholder="<?php esc_attr_e('Post parent in', 'wpdatatables'); ?>">
                        </div>
                    </div>
                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Post parent not in', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Exclude posts with specific parent posts.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="fg-line form-group m-b-0">
                            <input id="wdt-wp-query-post-parent-not-in" type="text"
                                   class="form-control input-sm wdt-wp-query-parameter"
                                   data-value="post_parent__not_in"
                                   placeholder="<?php esc_attr_e('Post parent not in', 'wpdatatables'); ?>">
                        </div>
                    </div>

                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Post in', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Limit the results to posts with specific IDs.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="fg-line form-group m-b-0">
                            <input id="wdt-wp-query-post-in" type="text"
                                   class="form-control input-sm wdt-wp-query-parameter" data-value="post__in"
                                   placeholder="<?php esc_attr_e('Post in', 'wpdatatables'); ?>">
                        </div>
                    </div>

                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Post not in', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Exclude posts with specific IDs from the results.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="fg-line form-group m-b-0">
                            <input id="wdt-wp-query-post-not-in" type="text"
                                   class="form-control input-sm wdt-wp-query-parameter" data-value="post__not_in"
                                   placeholder="<?php esc_attr_e('Post parent not in', 'wpdatatables'); ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Post name in', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Include posts that match specific post names.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="fg-line form-group m-b-0">
                            <input id="wdt-wp-query-post-name-in" type="text"
                                   class="form-control input-sm wdt-wp-query-parameter" data-value="post_name__in"
                                   placeholder="<?php esc_attr_e('Post name in', 'wpdatatables'); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div role="tabpanel" class="tab-pane fade" id="category-tag-settings-tab">
                <div class="row">
                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Category ID', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Display posts from categories with specific IDs.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="form-group">
                            <div class="fg-line wdt-custom-number-input">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                                        data-type="minus" data-field="wdt-category-id">
                                    <i class="wpdt-icon-minus"></i>
                                </button>
                                <input type="number" name="wdt-category-id" min="0"
                                       class="form-control input-sm input-number wdt-wp-query-parameter"
                                       id="wdt-wp-query-category-id"
                                       data-value="cat"
                                       placeholder="<?php esc_attr_e('Category ID', 'wpdatatables'); ?>">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                                        data-type="plus" data-field="wdt-category-id">
                                    <i class="wpdt-icon-plus-full"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Category slug', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Show posts from categories matching a specific slug.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="fg-line form-group m-b-0">
                            <input id="wdt-wp-query-category-slug" data-value="category_name" type="text"
                                   class="form-control input-sm wdt-wp-query-parameter"
                                   placeholder="<?php esc_attr_e('Category slug', 'wpdatatables'); ?>">
                        </div>
                    </div>

                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Category in', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Include posts from selected categories.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="select">
                            <select class="form-control selectpicker wdt-wp-query-parameter" multiple="multiple"
                                    title="<?php esc_attr_e('Select categories', 'wpdatatables'); ?>"
                                    id="wdt-wp-query-category-in" data-value="category__in">
                                <?php
                                $categories = get_categories(array('hide_empty' => false)); // Get all categories
                                foreach ($categories as $category) {
                                    echo '<option value="' . $category->term_id . '">' . esc_html($category->name) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Category not in', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Exclude posts from certain categories.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="select">
                            <select class="form-control selectpicker wdt-wp-query-parameter" multiple="multiple"
                                    title="<?php esc_attr_e('Select categories', 'wpdatatables'); ?>"
                                    id="wdt-wp-query-category-not-in" data-value="category__not_in">
                                <?php
                                foreach ($categories as $category) {
                                    echo '<option value="' . $category->term_id . '">' . esc_html($category->name) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                </div>
                <div class="row">
                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Tag slug', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Show posts tagged with specific slugs.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="fg-line form-group m-b-0">
                            <input id="wdt-wp-query-tag-slug" data-value="tag" type="text"
                                   class="form-control input-sm wdt-wp-query-parameter"
                                   placeholder="<?php esc_attr_e('Tag slug', 'wpdatatables'); ?>">
                        </div>
                    </div>

                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Tag ID', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Filter posts by tag IDs.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="form-group">
                            <div class="fg-line wdt-custom-number-input">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                                        data-type="minus" data-field="wdt-tag-id">
                                    <i class="wpdt-icon-minus"></i>
                                </button>
                                <input type="number" name="wdt-tag-id" min="0"
                                       class="form-control input-sm input-number wdt-wp-query-parameter"
                                       id="wdt-wp-query-tag-id"
                                       data-value="tag_id"
                                       placeholder="<?php esc_attr_e('Tag ID', 'wpdatatables'); ?>">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                                        data-type="plus" data-field="wdt-tag-id">
                                    <i class="wpdt-icon-plus-full"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Tag in', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Include posts from selected tags.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="select">
                            <select class="form-control selectpicker wdt-wp-query-parameter" multiple="multiple"
                                    title="<?php esc_attr_e('Select tags', 'wpdatatables'); ?>"
                                    id="wdt-wp-query-tag-in" data-value="tag__in">
                                <?php
                                $tags = get_tags(array('hide_empty' => false)); // Get all tags
                                foreach ($tags as $tag) {
                                    echo '<option value="' . $tag->term_id . '">' . esc_html($tag->name) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Tag not in', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Exclude posts from certain tags.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="select">
                            <select class="form-control selectpicker wdt-wp-query-parameter" multiple="multiple"
                                    title="<?php esc_attr_e('Select tags', 'wpdatatables'); ?>"
                                    id="wdt-wp-query-tag-not-in" data-value="tag__not_in">
                                <?php
                                foreach ($tags as $tag) {
                                    echo '<option value="' . $tag->term_id . '">' . esc_html($tag->name) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                </div>
            </div>

            <div role="tabpanel" class="tab-pane fade" id="comments-settings-tab">
                <div class="row">
                    <div class="col-sm-1">
                        <label class="wdt-query-label "><?php esc_html_e('Comment count', 'wpdatatables'); ?></label>
                    </div>
                    <div class="col-sm-2">
                        <div class="select">
                            <select class="form-control selectpicker wdt-wp-query-parameter"
                                    data-value="comment_count_compare"
                                    title="<?php esc_attr_e('=', 'wpdatatables'); ?>"
                                    id="wdt-wp-query-comment-count-compare">
                                <option value="=" selected="selected">=</option>
                                <option value="!=">!=</option>
                                <option value=">">></option>
                                <option value=">=">>=</option>
                                <option value="<"><</option>
                                <option value="<="><=</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <div class="fg-line wdt-custom-number-input">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                                        data-type="minus" data-field="wdt-wp-query-comments">
                                    <i class="wpdt-icon-minus"></i>
                                </button>
                                <input type="number" name="wdt-wp-query-comments" min="0"
                                       class="form-control wdt-wp-query-parameter input-sm input-number"
                                       id="wdt-wp-query-comments"
                                       data-value="comment_count_value"
                                       placeholder="<?php esc_attr_e('Comment number', 'wpdatatables'); ?>">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                                        data-type="plus" data-field="wdt-wp-query-comments">
                                    <i class="wpdt-icon-plus-full"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div role="tabpanel" class="tab-pane fade" id="author-settings-tab">
                <div class="row">
                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Author ID', 'wpdatatables'); ?>
                            <i class=" wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Display posts by a specific author.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="form-group">
                            <div class="fg-line wdt-custom-number-input">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-minus"
                                        data-type="minus" data-field="wdt-author-id">
                                    <i class="wpdt-icon-minus"></i>
                                </button>
                                <input type="number" name="wdt-author-id" min="0"
                                       class="form-control input-sm input-number wdt-wp-query-parameter"
                                       id="wdt-wp-query-author-id"
                                       data-value="author"
                                       placeholder="<?php esc_attr_e('Author ID', 'wpdatatables'); ?>">
                                <button type="button" class="btn btn-default wdt-btn-number wdt-button-plus"
                                        data-type="plus" data-field="wdt-author-id">
                                    <i class="wpdt-icon-plus-full"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Author name', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Filter posts by the author’s name.', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="fg-line form-group m-b-0">
                            <input id="wdt-wp-query-author-name" type="text"
                                   class="form-control input-sm wdt-wp-query-parameter" data-value="author_name"
                                   placeholder="<?php esc_attr_e('Author name', 'wpdatatables'); ?>">
                        </div>
                    </div>

                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Author in', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Include posts from selected authors by ID (should be comma-separated values to match, e.g. "1, 10, 20").', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="fg-line form-group m-b-0">
                            <input id="wdt-wp-query-author-in" type="text"
                                   class="form-control input-sm wdt-wp-query-parameter" data-value="author__in"
                                   placeholder="<?php esc_attr_e('Author in', 'wpdatatables'); ?>">
                        </div>
                    </div>

                    <div class="col-sm-2-0 m-b-16">
                        <h4 class="c-title-color m-b-2">
                            <?php esc_html_e('Author not in', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Exclude posts by certain authors by ID (should be comma-separated values to match, e.g. "1, 10, 20").', 'wpdatatables'); ?>"></i>
                        </h4>
                        <div class="fg-line form-group m-b-0">
                            <input id="wdt-wp-query-author-not-in" type="text"
                                   class="form-control input-sm wdt-wp-query-parameter" data-value="author__not_in"
                                   placeholder="<?php esc_attr_e('Author not in', 'wpdatatables'); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div role="tabpanel" class="tab-pane fade" id="cf-settings-tab">
                <div class="row">
                    <div id="wdt-wp-query-cf-container">
                    </div>

                    <div class="col-sm-12 p-l-0 m-t-10">
                        <button class="btn pull-left wdt-wp-query-add-cf-column">
                            <i class="wpdt-icon-plus-thin"></i> <?php esc_html_e('Add New Custom Field Column', 'wpdatatables'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script id="wdt-wp-query-tax-template" type="text/x-jsrender">
    <div class="card-body bg-white wdt-wp-query-clause-template">
        <div class="row ">
            <div class="col-sm-2-0 m-b-16">
                <h4 class="c-title-color m-b-2">
    <?php esc_html_e('Taxonomy', 'wpdatatables'); ?>
    <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
       title="<?php esc_attr_e('Choose a taxonomy (like categories or tags) to filter posts by their terms.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="fg-line form-group m-b-0">
                <input id="wdt-wp-query-taxonomy-{{>taxClauseId}}" data-count={{>taxClauseId}} type="text"
                       class="form-control input-sm wdt_wp_query_tax_parameter wdt-wp-query-taxonomy" data-value="taxonomy"
                       placeholder="<?php esc_attr_e('Taxonomy', 'wpdatatables'); ?>">
            </div>
        </div>

        <div class="col-sm-2-0 m-b-16">
            <h4 class="c-title-color m-b-2">
    <?php esc_html_e('Field', 'wpdatatables'); ?>
    <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
       title="<?php esc_attr_e('Select the taxonomy field (like term ID or slug) to match against the terms.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="fg-line form-group m-b-0">
                <input id="wdt-wp-query-tax-field-{{>taxClauseId}}" data-count={{>taxClauseId}} type="text"
                       class="form-control input-sm wdt_wp_query_tax_parameter wdt-wp-query-tax-field" data-value="field"
                       placeholder="<?php esc_attr_e('Field', 'wpdatatables'); ?>">
            </div>
        </div>

        <div class="col-sm-2-0 m-b-16">
            <h4 class="c-title-color m-b-2">
    <?php esc_html_e('Terms', 'wpdatatables'); ?>
    <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
       title="<?php esc_attr_e('Specify the terms to include posts that match these taxonomy terms.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="fg-line form-group m-b-0">
                <input id="wdt-wp-query-tax-terms-{{>taxClauseId}}" data-count={{>taxClauseId}} type="text"
                       class="form-control input-sm wdt_wp_query_tax_parameter wdt-wp-query-tax-terms" data-value="terms"
                       placeholder="<?php esc_attr_e('Terms', 'wpdatatables'); ?>">
            </div>
        </div>

                <div class="col-sm-2-0 m-b-16">
            <h4 class="c-title-color m-b-2">
    <?php esc_html_e('Include children', 'wpdatatables'); ?>
    <i class="wpdt-icon-info-circle-thin " data-toggle="tooltip" data-placement="right"
       title="<?php esc_attr_e('Include child terms in the taxonomy filter.', 'wpdatatables'); ?>"></i>
            </h4>

            <div class="toggle-switch wdt-wp-query-include-children" data-ts-color="blue">
                <input class="wdt_wp_query_tax_parameter wdt-checkbox-parameter" type="checkbox"
                 data-value="include_children" data-count={{>taxClauseId}}
                 id="wdt-wp-query-include-children-{{>taxClauseId}}">
                <label for="wdt-wp-query-include-children-{{>taxClauseId}}"
                       class="ts-label"><?php esc_html_e('Include children', 'wpdatatables'); ?></label>
            </div>
        </div>

                <div class="col-sm-1 p-r-0 p-l-0 text-center">
            <ul class="actions p-r-5">
                <li class="p-t-30" id="wdt-constructor-delete-tax-clause">
                    <a>
                        <i class="wpdt-icon-trash"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>

</div>

</script>

<script id="wdt-wp-query-meta-template" type="text/x-jsrender">
    <div class="card-body bg-white wdt-wp-query-meta-template wdt-wp-query-clause-template">
        <div class="row ">
            <div class="col-sm-2-0 m-b-16">
                <h4 class="c-title-color m-b-2">
    <?php esc_html_e('Custom field key', 'wpdatatables'); ?>
    <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
       title="<?php esc_attr_e('Enter the meta key to filter posts with a specific custom field.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="fg-line form-group m-b-0">
    <input id="wdt-wp-query-meta-{{>metaFieldId}}" data-count={{>metaFieldId}} type="text"
           class="form-control input-sm wdt_wp_query_meta_parameter wdt-wp-query-meta-key" data-value="key"
           placeholder="<?php esc_attr_e('Custom field key', 'wpdatatables'); ?>">
            </div>
        </div>

        <div class="col-sm-2-0 m-b-16">
            <h4 class="c-title-color m-b-2">
    <?php esc_html_e('Value', 'wpdatatables'); ?>
    <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
       title="<?php esc_attr_e('Specify the value associated with the custom field key.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="fg-line form-group m-b-0">
                <input id="wdt-wp-query-meta-value-{{>metaFieldId}}" data-count={{>metaFieldId}} type="text"
                       class="form-control input-sm wdt_wp_query_meta_parameter wdt-wp-query-meta-value" data-value="value"
                       placeholder="<?php esc_attr_e('Value', 'wpdatatables'); ?>">
            </div>
        </div>

                <div class="col-sm-2-0 m-b-16">
            <h4 class="c-title-color m-b-2">
    <?php esc_html_e('Compare', 'wpdatatables'); ?>
    <i class="wpdt-icon-info-circle-thin " data-toggle="tooltip" data-placement="right"
       title="<?php esc_attr_e('Operator to test.', 'wpdatatables'); ?>"></i>
            </h4>
                    <div>
                        <select class="selectpicker wdt_wp_query_meta_parameter"
                          data-value="compare" data-count={{>metaFieldId}}
                            title="<?php esc_attr_e('=', 'wpdatatables'); ?>">
                        <option value="=" selected="selected">=</option>
                        <option value="!=">!=</option>
                        <option value=">">></option>
                        <option value=">=">>=</option>
                        <option value="<"><</option>
                        <option value="<="><=</option>
                        <option value="LIKE">LIKE</option>
                        <option value="NOT LIKE">NOT LIKE</option>
                        <option value="IN">IN</option>
                        <option value="NOT IN">NOT IN</option>
                        <option value="BETWEEN">BETWEEN</option>
                        <option value="NOT BETWEEN">NOT BETWEEN</option>
                        <option value="EXISTS">EXISTS</option>
                        <option value="NOT EXISTS">NOT EXISTS</option>
                        </select>
                    </div>
                </div>

                <div class="col-sm-2-0 m-b-16">
            <h4 class="c-title-color m-b-2">
    <?php esc_html_e('Type', 'wpdatatables'); ?>
    <i class="wpdt-icon-info-circle-thin " data-toggle="tooltip" data-placement="right"
       title="<?php esc_attr_e('Define the data type of the custom field, such as numeric or string.', 'wpdatatables'); ?>"></i>
            </h4>
        <div>
                    <select class="selectpicker wdt_wp_query_meta_parameter"
                                  data-value="type" data-count={{>metaFieldId}}
                                    title="<?php esc_attr_e('CHAR', 'wpdatatables'); ?>"
                              id="wdt-wp-query-meta-type-{{>metaFieldId}}">
                                <option value="CHAR" selected="selected">CHAR</option>
                                <option value="NUMERIC">NUMERIC</option>
                                <option value="BINARY">BINARY</option>
                                <option value="DATE">DATE</option>
                                <option value="TIME">TIME</option>
                                <option value="DATETIME">DATETIME</option>
                                <option value="DECIMAL">DECIMAL</option>
                                <option value="SIGNED">SIGNED</option>
                                <option value="UNSIGNED">UNSIGNED</option>
                            </select>
        </div>
                </div>

                <div class="col-sm-1 p-r-0 p-l-0 text-center">
            <ul class="actions p-r-5">
                <li class="p-t-30" id="wdt-constructor-delete-custom-field">
                    <a>
                        <i class="wpdt-icon-trash"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>

</div>

</script>

<script id="wdt-wp-query-date-template" type="text/x-jsrender">
    <div class="card-body bg-white wdt-wp-query-clause-template wdt-wp-query-date-template">
        <div class="row">
            <div class="col-sm-2-0 m-b-16">
                <h4 class="c-title-color m-b-2"><?php esc_html_e('Year', 'wpdatatables'); ?></h4>
                <div class="fg-line form-group m-b-0">
                    <input id="wdt-wp-query-year-{{>dateClauseId}}" data-count={{>dateClauseId}} type="number"
                           class="form-control input-sm wdt_wp_query_date_parameter wdt-wp-query-year" data-value="year"
                           placeholder="<?php esc_attr_e('Year', 'wpdatatables'); ?>" min="1000" max="9999">
                </div>
            </div>

            <div class="col-sm-2-0 m-b-16">
                <h4 class="c-title-color m-b-2"><?php esc_html_e('Month', 'wpdatatables'); ?></h4>
                <div class="fg-line form-group m-b-0">
                    <input id="wdt-wp-query-month-{{>dateClauseId}}" data-count={{>dateClauseId}} type="number"
                           class="form-control input-sm wdt_wp_query_date_parameter wdt-wp-query-month" data-value="month"
                           placeholder="<?php esc_attr_e('Month', 'wpdatatables'); ?>" min="1" max="12">
                </div>
            </div>

            <div class="col-sm-2-0 m-b-16">
                <h4 class="c-title-color m-b-2"><?php esc_html_e('Week', 'wpdatatables'); ?></h4>
                <div class="fg-line form-group m-b-0">
                    <input id="wdt-wp-query-week-{{>dateClauseId}}" data-count={{>dateClauseId}} type="number"
                           class="form-control input-sm wdt_wp_query_date_parameter wdt-wp-query-week" data-value="week"
                           placeholder="<?php esc_attr_e('Week', 'wpdatatables'); ?>" min="0" max="53">
                </div>
            </div>

            <div class="col-sm-2-0 m-b-16">
                <h4 class="c-title-color m-b-2"><?php esc_html_e('Day', 'wpdatatables'); ?></h4>
                <div class="fg-line form-group m-b-0">
                    <input id="wdt-wp-query-day-{{>dateClauseId}}" data-count={{>dateClauseId}} type="number"
                           class="form-control input-sm wdt_wp_query_date_parameter wdt-wp-query-day" data-value="day"
                           placeholder="<?php esc_attr_e('Day', 'wpdatatables'); ?>" min="1" max="31">
                </div>
            </div>

            <div class="col-sm-2-0 m-b-16">
                <h4 class="c-title-color m-b-2"><?php esc_html_e('Hour', 'wpdatatables'); ?></h4>
                <div class="fg-line form-group m-b-0">
                    <input id="wdt-wp-query-hour-{{>dateClauseId}}" data-count={{>dateClauseId}} type="number"
                           class="form-control input-sm wdt_wp_query_date_parameter wdt-wp-query-hour" data-value="hour"
                           placeholder="<?php esc_attr_e('Hour', 'wpdatatables'); ?>" min="0" max="23">
                </div>
            </div>

            <div class="col-sm-2-0 m-b-16">
                <h4 class="c-title-color m-b-2"><?php esc_html_e('Minute', 'wpdatatables'); ?></h4>
                <div class="fg-line form-group m-b-0">
                    <input id="wdt-wp-query-minute-{{>dateClauseId}}" data-count={{>dateClauseId}} type="number"
                           class="form-control input-sm wdt_wp_query_date_parameter wdt-wp-query-minute" data-value="minute"
                           placeholder="<?php esc_attr_e('Minute', 'wpdatatables'); ?>" min="0" max="59">
                </div>
            </div>

            <div class="col-sm-2-0 m-b-16">
                <h4 class="c-title-color m-b-2"><?php esc_html_e('Second', 'wpdatatables'); ?></h4>
                <div class="fg-line form-group m-b-0">
                    <input id="wdt-wp-query-second-{{>dateClauseId}}" data-count={{>dateClauseId}} type="number"
                           class="form-control input-sm wdt_wp_query_date_parameter wdt-wp-query-second" data-value="second"
                           placeholder="<?php esc_attr_e('Second', 'wpdatatables'); ?>">
                </div>
            </div>

            <div class="col-sm-2-0 m-b-16">
                <h4 class="c-title-color m-b-2"><?php esc_html_e('After', 'wpdatatables'); ?></h4>
                <div class="fg-line form-group m-b-0">
                    <input id="wdt-wp-query-after-{{>dateClauseId}}" data-count={{>dateClauseId}} type="text"
                           class="form-control input-sm wdt_wp_query_date_parameter wdt-wp-query-after" data-value="after"
                           placeholder="<?php esc_attr_e('After', 'wpdatatables'); ?>">
                </div>
            </div>

            <div class="col-sm-2-0 m-b-16">
                <h4 class="c-title-color m-b-2"><?php esc_html_e('Before', 'wpdatatables'); ?></h4>
                <div class="fg-line form-group m-b-0">
                    <input id="wdt-wp-query-before-{{>dateClauseId}}" data-count={{>dateClauseId}} type="text"
                           class="form-control input-sm wdt_wp_query_date_parameter wdt-wp-query-before" data-value="before"
                           placeholder="<?php esc_attr_e('Before', 'wpdatatables'); ?>">
                </div>
            </div>

            <div class="col-sm-2-0 m-b-16">
                <h4 class="c-title-color m-b-2"><?php esc_html_e('Inclusive', 'wpdatatables'); ?></h4>
                <div class="toggle-switch wdt-wp-query-inclusive" data-ts-color="blue">
                    <input class="wdt_wp_query_date_parameter wdt-checkbox-parameter" type="checkbox"
                           data-value="inclusive" data-count={{>dateClauseId}}
                           id="wdt-wp-query-inclusive-{{>dateClauseId}}">
                    <label for="wdt-wp-query-inclusive-{{>dateClauseId}}"
                           class="ts-label"><?php esc_html_e('Inclusive', 'wpdatatables'); ?></label>
                </div>
            </div>

            <div class="col-sm-2-0 m-b-16">
                <h4 class="c-title-color m-b-2"><?php esc_html_e('Compare', 'wpdatatables'); ?></h4>
             <div>
                        <select class="selectpicker wdt_wp_query_date_parameter"
                          data-value="compare" data-count={{>dateClauseId}}
                            title="<?php esc_attr_e('=', 'wpdatatables'); ?>">
                        <option value="=" selected="selected">=</option>
                        <option value="!=">!=</option>
                        <option value=">">></option>
                        <option value=">=">>=</option>
                        <option value="<"><</option>
                        <option value="<="><=</option>
                        <option value="IN">IN</option>
                        <option value="NOT IN">NOT IN</option>
                        <option value="BETWEEN">BETWEEN</option>
                        <option value="NOT BETWEEN">NOT BETWEEN</option>
                        </select>
                    </div>
            </div>

            <div class="col-sm-2-0 m-b-16">
                <h4 class="c-title-color m-b-2"><?php esc_html_e('Column', 'wpdatatables'); ?></h4>
                <div class="fg-line form-group m-b-0">
                    <input id="wdt-wp-query-column-{{>dateClauseId}}" data-count={{>dateClauseId}} type="text"
                           class="form-control input-sm wdt_wp_query_date_parameter wdt-wp-query-column" data-value="column"
                           placeholder="<?php esc_attr_e('Column', 'wpdatatables'); ?>">
                </div>
            </div>

            <div class="col-sm-1 p-r-0 p-l-0 text-center">
                <ul class="actions p-r-5">
                    <li class="p-t-30" id="wdt-constructor-delete-date-clause">
                        <a>
                            <i class="wpdt-icon-trash"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

</script>

<?php
if (defined('WDT_WP_QUERY_PATH')) {
    include WDT_WP_QUERY_PATH . 'templates/custom_field_columns_block.inc.php';
}
?>
