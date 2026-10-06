<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php
/**
 * AI Chart Type Suggester — chart wizard step 1 panel.
 *
 * Bound by assets/js/ai/wdt-ai-chart-suggester.js.
 *
 * @var bool   $wdt_ai_available
 * @var array  $wdt_ai_models
 * @var string $wdt_ai_default_model
 * @var string $wdt_ai_unavailable_message
 * @var string $wdt_ai_settings_url
 * @var string $wdt_ai_settings_action
 * @var array  $wdt_ai_tables
 */

$wdt_ai_icon_url = WDT_ASSETS_PATH . 'img/constructor/generate-table-with-ai.svg';
$wdt_ai_tooltip  = __(
    'Describe what you want to visualize and pick a wpDataTable. AI recommends a render engine, chart type, title, and axis columns you can review before applying to the wizard.',
    'wpdatatables'
);
?>

<div class="col-sm-12 p-0 wdt-ai-chart-suggester-section">

    <?php if ($wdt_ai_available) : ?>

        <div class="card wdt-ai-chart-card wdt-ai-chart-card--active"
             data-alternatives-label="<?php esc_attr_e('Alternatives', 'wpdatatables'); ?>">
            <div class="wdt-ai-chart-card__head">
                <img class="wdt-ai-chart-card__icon"
                     src="<?php echo esc_url($wdt_ai_icon_url); ?>"
                     alt="">
                <h4 class="c-title-color m-t-0 m-b-0 f-14">
                    <?php esc_html_e('Suggest a chart with AI', 'wpdatatables'); ?>
                    <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                       title="<?php echo esc_attr($wdt_ai_tooltip); ?>"></i>
                </h4>
            </div>

            <div class="wdt-ai-chart-card__body">
                <div class="form-group m-b-15">
                    <div class="fg-line">
                        <textarea class="form-control wdt-ai-cs-description" rows="2"
                                  placeholder="<?php esc_attr_e('e.g. Track AAPL price over time', 'wpdatatables'); ?>"></textarea>
                    </div>
                </div>

                <div class="wdt-ai-cs-controls">
                    <div class="wdt-ai-cs-control wdt-ai-cs-control--table">
                        <h4 class="c-title-color m-b-2 f-14">
                            <?php esc_html_e('Data table', 'wpdatatables'); ?>
                        </h4>
                        <div class="form-group m-b-0">
                            <select class="selectpicker wdt-ai-dropdown wdt-ai-cs-table" id="wdt-ai-cs-table"
                                    data-live-search="true" data-container="body" data-width="100%">
                                <option value=""><?php esc_html_e('Pick a wpDataTable', 'wpdatatables'); ?></option>
                                <?php foreach ((array) $wdt_ai_tables as $wdt_ai_table) : ?>
                                    <option value="<?php echo esc_attr($wdt_ai_table['id']); ?>">
                                        <?php echo esc_html($wdt_ai_table['title'] . ' (id: ' . $wdt_ai_table['id'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <?php if (!empty($wdt_ai_models)) : ?>
                        <div class="wdt-ai-cs-control wdt-ai-cs-control--model">
                            <h4 class="c-title-color m-b-2 f-14">
                                <?php esc_html_e('Model', 'wpdatatables'); ?>
                            </h4>
                            <div class="form-group m-b-0">
                                <select class="selectpicker wdt-ai-dropdown wdt-ai-cs-model" id="wdt-ai-cs-model"
                                        data-container="body" data-width="100%">
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

                    <div class="wdt-ai-cs-control wdt-ai-cs-control--actions">
                        <button type="button" class="btn btn-primary wdt-ai-cs-generate">
                            <?php esc_html_e('Suggest', 'wpdatatables'); ?>
                        </button>
                        <span class="wdt-ai-cs-status text-muted"></span>
                    </div>
                </div>

                <div class="wdt-ai-cs-result hidden m-t-20">
                    <div class="wdt-ai-cs-warnings text-danger m-b-10 hidden"></div>

                    <div class="row">
                        <div class="col-sm-5">
                            <h4 class="c-title-color m-b-2 f-14">
                                <?php esc_html_e('Suggested title', 'wpdatatables'); ?>
                            </h4>
                            <div class="form-group">
                                <div class="fg-line">
                                    <input type="text" class="form-control input-sm wdt-ai-cs-title" value="">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <h4 class="c-title-color m-b-2 f-14">
                                <?php esc_html_e('Render engine', 'wpdatatables'); ?>
                            </h4>
                            <div class="form-group">
                                <select class="wdt-ai-dropdown wdt-ai-cs-pending-picker wdt-ai-cs-engine"
                                        data-width="100%">
                                    <option value="google"><?php esc_html_e('Google Charts', 'wpdatatables'); ?></option>
                                    <option value="chartjs"><?php esc_html_e('Chart.js', 'wpdatatables'); ?></option>
                                    <option value="highcharts"><?php esc_html_e('HighCharts', 'wpdatatables'); ?></option>
                                    <option value="apexcharts"><?php esc_html_e('ApexCharts', 'wpdatatables'); ?></option>
                                    <option value="highstock"><?php esc_html_e('HighCharts Stock', 'wpdatatables'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <h4 class="c-title-color m-b-2 f-14">
                                <?php esc_html_e('Chart type', 'wpdatatables'); ?>
                            </h4>
                            <div class="form-group">
                                <select class="wdt-ai-dropdown wdt-ai-cs-pending-picker wdt-ai-cs-chart-type"
                                        data-width="100%">
                                    <option value="line"><?php esc_html_e('Line', 'wpdatatables'); ?></option>
                                    <option value="bar"><?php esc_html_e('Bar', 'wpdatatables'); ?></option>
                                    <option value="column"><?php esc_html_e('Column', 'wpdatatables'); ?></option>
                                    <option value="area"><?php esc_html_e('Area', 'wpdatatables'); ?></option>
                                    <option value="pie"><?php esc_html_e('Pie', 'wpdatatables'); ?></option>
                                    <option value="donut"><?php esc_html_e('Donut', 'wpdatatables'); ?></option>
                                    <option value="scatter"><?php esc_html_e('Scatter', 'wpdatatables'); ?></option>
                                    <option value="mixed"><?php esc_html_e('Mixed / column', 'wpdatatables'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <p class="wdt-ai-cs-reason text-muted m-b-10"></p>
                    <div class="wdt-ai-cs-alternatives m-b-15 hidden"></div>

                    <div class="row">
                        <div class="col-sm-4">
                            <h4 class="c-title-color m-b-2 f-14">
                                <?php esc_html_e('X-axis / label', 'wpdatatables'); ?>
                            </h4>
                            <div class="form-group">
                                <select class="wdt-ai-dropdown wdt-ai-cs-pending-picker wdt-ai-cs-x-axis"
                                        data-live-search="true" data-width="100%"></select>
                            </div>
                        </div>
                        <div class="col-sm-8">
                            <h4 class="c-title-color m-b-2 f-14">
                                <?php esc_html_e('Y-axis / series', 'wpdatatables'); ?>
                            </h4>
                            <div class="form-group">
                                <select class="wdt-ai-dropdown wdt-ai-cs-pending-picker wdt-ai-cs-y-axis" multiple
                                        data-live-search="true" data-width="100%"
                                        data-selected-text-format="count > 2"
                                        data-none-selected-text="<?php esc_attr_e('No numeric columns', 'wpdatatables'); ?>"></select>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-primary wdt-ai-cs-apply m-t-5">
                        <?php esc_html_e('Use this chart', 'wpdatatables'); ?>
                    </button>
                </div>
            </div>
        </div>

    <?php else : ?>

        <div class="card wdt-ai-unavailable-card wdt-ai-chart-card wdt-premium-feature">
            <div class="wdt-ai-chart-card__head opacity-6">
                <img class="wdt-ai-chart-card__icon"
                     src="<?php echo esc_url($wdt_ai_icon_url); ?>"
                     alt="">
                <h4 class="m-t-0 m-b-0 f-14">
                    <span class="opacity-6"><?php esc_html_e('Suggest a chart with AI', 'wpdatatables'); ?>.</span>
                    <i class="wpdt-icon-star-full" style="color: #091D70;"></i>
                    <span class="f-14" style="opacity:1 !important;font-weight:bold;color:#091D70;">
                        <?php esc_html_e('Requires WordPress AI setup', 'wpdatatables'); ?>
                    </span>
                </h4>
            </div>
            <div class="wdt-ai-chart-card__body">
                <span class="opacity-6"><?php echo esc_html($wdt_ai_unavailable_message); ?></span>
                <div class="m-t-15">
                    <a href="<?php echo esc_url($wdt_ai_settings_url); ?>"
                       class="btn btn-primary wdt-upgrade-btn">
                        <?php echo esc_html($wdt_ai_settings_action); ?>
                    </a>
                </div>
            </div>
        </div>

    <?php endif; ?>

    <div class="wdt-constructor-section-divider m-t-25 m-b-25">
        <span><?php esc_html_e('or choose a chart type below', 'wpdatatables'); ?></span>
    </div>

</div>
