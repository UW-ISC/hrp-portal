<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php
$wdt_ai_icon_url = WDT_ASSETS_PATH . 'img/constructor/generate-table-with-ai.svg';
$wdt_ai_tooltip  = __(
    'Describe the table you need in plain English. AI can suggest a manual column scaffold or a SQL query-backed table you can review before saving.',
    'wpdatatables'
);
?>

<div class="col-sm-12 p-0 wdt-ai-table-generator-section">

    <div class="row wpdt-flex wdt-first-row">
        <div class="wdt-constructor-type-selecter-block col-sm-12">

            <?php if ($wdt_ai_available) : ?>

                <div class="card wdt-ai-generate-panel wdt-ai-generate-panel--active wdt-ai-constructor-card">
                    <div class="card-header">
                        <img class="img-responsive"
                             src="<?php echo esc_url($wdt_ai_icon_url); ?>"
                             alt="<?php esc_attr_e('Generate a table with AI', 'wpdatatables'); ?>">
                    </div>
                    <div class="card-body p-b-20 p-r-20 p-t-20">
                        <h4 class="c-title-color m-t-0 m-b-10 f-14">
                            <?php esc_html_e('Generate a table with AI', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php echo esc_attr($wdt_ai_tooltip); ?>"></i>
                        </h4>

                        <div class="form-group m-b-15">
                            <div class="fg-line">
                                <textarea class="form-control wdt-ai-tg-description" rows="3"
                                          placeholder="<?php esc_attr_e('e.g. A list of company employees with name, department, salary, hire date and email', 'wpdatatables'); ?>"></textarea>
                            </div>
                        </div>

                        <div class="wdt-ai-tg-controls">
                            <div class="wdt-ai-tg-control wdt-ai-tg-control--type">
                                <h4 class="c-title-color m-b-2 f-14">
                                    <?php esc_html_e('Output type', 'wpdatatables'); ?>
                                    <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                       title="<?php esc_attr_e('Manual creates an editable column scaffold. SQL creates a query-backed table linked to your database.', 'wpdatatables'); ?>"></i>
                                </h4>
                                <div class="form-group m-b-0">
                                    <select class="selectpicker wdt-ai-dropdown wdt-ai-tg-type" id="wdt-ai-tg-table-type" data-width="100%">
                                        <option value="manual"><?php esc_html_e('Manual table', 'wpdatatables'); ?></option>
                                        <option value="sql"><?php esc_html_e('SQL query table', 'wpdatatables'); ?></option>
                                    </select>
                                </div>
                            </div>

                            <?php if (!empty($wdt_ai_models)) : ?>
                                <div class="wdt-ai-tg-control wdt-ai-tg-control--model">
                                    <h4 class="c-title-color m-b-2 f-14">
                                        <?php esc_html_e('Model', 'wpdatatables'); ?>
                                        <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                                           title="<?php esc_attr_e('AI model used for generation. Only models configured in WordPress AI settings are listed.', 'wpdatatables'); ?>"></i>
                                    </h4>
                                    <div class="form-group m-b-0">
                                        <select class="selectpicker wdt-ai-dropdown wdt-ai-tg-model" id="wdt-ai-tg-model" data-width="100%">
                                            <?php foreach ($wdt_ai_models as $wdt_ai_model) : ?>
                                                <?php
                                                $wdt_ai_model_label = !empty($wdt_ai_model['provider'])
                                                    ? $wdt_ai_model['provider'] . ' — ' . $wdt_ai_model['name']
                                                    : $wdt_ai_model['name'];
                                                ?>
                                                <option value="<?php echo esc_attr($wdt_ai_model['id']); ?>"
                                                    <?php selected($wdt_ai_model['id'], $wdt_ai_default_model); ?>>
                                                    <?php echo esc_html($wdt_ai_model_label); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="wdt-ai-tg-control wdt-ai-tg-control--actions">
                                <button type="button" class="btn btn-primary wdt-ai-tg-generate">
                                    <?php esc_html_e('Generate', 'wpdatatables'); ?>
                                </button>
                                <span class="wdt-ai-tg-status text-muted"></span>
                            </div>
                        </div>

                        <div class="wdt-ai-tg-result hidden m-t-20">
                            <div class="wdt-ai-tg-warnings text-danger m-b-10 hidden"></div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <h4 class="c-title-color m-b-2 f-14">
                                        <?php esc_html_e('Suggested title', 'wpdatatables'); ?>
                                    </h4>
                                    <div class="form-group">
                                        <div class="fg-line">
                                            <input type="text" class="form-control input-sm wdt-ai-tg-title" value="">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive m-b-10">
                                <table class="table table-bordered wdt-ai-tg-columns">
                                    <thead>
                                    <tr>
                                        <th><?php esc_html_e('Column name', 'wpdatatables'); ?></th>
                                        <th style="width:160px;"><?php esc_html_e('Type', 'wpdatatables'); ?></th>
                                        <th style="width:90px;"><?php esc_html_e('Filter', 'wpdatatables'); ?></th>
                                        <th style="width:50px;"></th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            <button type="button" class="btn btn-default btn-sm wdt-ai-tg-add-col m-b-15">
                                <?php esc_html_e('+ Add column', 'wpdatatables'); ?>
                            </button>

                            <div class="wdt-ai-tg-sql-wrap hidden">
                                <h4 class="c-title-color m-b-2 f-14">
                                    <?php esc_html_e('Suggested SQL', 'wpdatatables'); ?>
                                </h4>
                                <div class="form-group">
                                    <div class="fg-line">
                                        <textarea class="form-control wdt-ai-tg-sql" rows="4" style="font-family:monospace;"></textarea>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-primary wdt-ai-tg-apply">
                                <?php esc_html_e('Use this table', 'wpdatatables'); ?>
                            </button>
                        </div>
                    </div>
                </div>

            <?php else : ?>

                <div class="card wdt-ai-unavailable-card wdt-ai-constructor-card wdt-premium-feature">
                    <div class="card-header opacity-6">
                        <img class="img-responsive"
                             src="<?php echo esc_url($wdt_ai_icon_url); ?>"
                             alt="<?php esc_attr_e('Generate a table with AI', 'wpdatatables'); ?>">
                    </div>
                    <div class="card-body p-b-20 p-r-20 p-t-20">
                        <h4 class="m-t-0 m-b-8 f-14">
                            <span class="opacity-6 f-14"><?php esc_html_e('Generate a table with AI', 'wpdatatables'); ?>.</span>
                            <i class="wpdt-icon-star-full" style="color: #091D70;"></i>
                            <span class="f-14" style="opacity:1 !important;font-weight:bold;color:#091D70;">
                                <?php esc_html_e('Requires WordPress AI setup', 'wpdatatables'); ?>
                            </span>
                        </h4>
                        <span class="opacity-6"><?php echo esc_html($wdt_ai_unavailable_message); ?></span>
                    </div>
                    <a href="<?php echo esc_url($wdt_ai_settings_url); ?>"
                       class="btn btn-primary wdt-upgrade-btn">
                        <?php echo esc_html($wdt_ai_settings_action); ?>
                    </a>
                </div>

            <?php endif; ?>

        </div>
    </div>

    <div class="wdt-constructor-section-divider m-t-25 m-b-25">
        <span><?php esc_html_e('or choose a table type below', 'wpdatatables'); ?></span>
    </div>

</div>

<span class="wdt-ai-tg-type-options hidden">
    <option value="string">string</option>
    <option value="int">int</option>
    <option value="float">float</option>
    <option value="date">date</option>
    <option value="datetime">datetime</option>
    <option value="time">time</option>
    <option value="url">url</option>
    <option value="email">email</option>
</span>
