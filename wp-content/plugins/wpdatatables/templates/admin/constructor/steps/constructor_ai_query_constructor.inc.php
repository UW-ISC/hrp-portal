<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php
/**
 * AI Query Constructor Assistant — Feature 2 (WP / MySQL GUI builders).
 *
 * Same visual language as Feature 3 (AI SQL Assistant): prompt → Model
 * selectpicker → Ask AI → review → Apply. Bound by
 * assets/js/ai/wdt-ai-query-constructor.js.
 *
 * @var bool   $wdt_ai_available
 * @var array  $wdt_ai_models
 * @var string $wdt_ai_default_model
 * @var string $wdt_ai_builder_type  `wp` or `mysql`
 */

if (empty($wdt_ai_available) || empty($wdt_ai_builder_type)) {
    return;
}

$wdt_ai_qc_title = ($wdt_ai_builder_type === 'wp')
    ? __('AI Query Assistant — WordPress database', 'wpdatatables')
    : __('AI Query Assistant — MySQL database', 'wpdatatables');

$wdt_ai_qc_tooltip = __(
    'Describe the report you need. AI suggests tables, columns, and filters you can apply to the picker cards below.',
    'wpdatatables'
);

$wdt_ai_qc_placeholder = ($wdt_ai_builder_type === 'wp')
    ? __('e.g. Published posts with comment counts from the last 30 days', 'wpdatatables')
    : __('e.g. Orders with customer email and total from the last 30 days', 'wpdatatables');
?>

<div class="wdt-ai-query-constructor wdt-ai-assistant-panel m-b-20"
     data-builder-type="<?php echo esc_attr($wdt_ai_builder_type); ?>">
    <h4 class="c-title-color m-t-0 m-b-10 f-14">
        <?php echo esc_html($wdt_ai_qc_title); ?>
        <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
           title="<?php echo esc_attr($wdt_ai_qc_tooltip); ?>"></i>
    </h4>

    <div class="form-group m-b-15">
        <div class="fg-line">
            <textarea class="form-control wdt-ai-qc-description" rows="2"
                      placeholder="<?php echo esc_attr($wdt_ai_qc_placeholder); ?>"></textarea>
        </div>
    </div>

    <div class="wdt-ai-assistant-controls">
        <?php if (!empty($wdt_ai_models)) : ?>
            <div class="wdt-ai-assistant-control wdt-ai-assistant-control--model">
                <h4 class="c-title-color m-b-2 f-14">
                    <?php esc_html_e('Model', 'wpdatatables'); ?>
                    <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                       title="<?php esc_attr_e('AI model used for suggestions. Only models configured in WordPress AI settings are listed.', 'wpdatatables'); ?>"></i>
                </h4>
                <div class="form-group m-b-0">
                    <select class="selectpicker wdt-ai-dropdown wdt-ai-qc-model" data-width="100%">
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

        <div class="wdt-ai-assistant-control wdt-ai-assistant-control--actions">
            <button type="button" class="btn btn-primary wdt-ai-qc-run">
                <?php esc_html_e('Ask AI', 'wpdatatables'); ?>
            </button>
            <span class="wdt-ai-qc-status text-muted"></span>
        </div>
    </div>

    <div class="wdt-ai-qc-result hidden m-t-15">
        <div class="wdt-ai-qc-warnings text-danger m-b-8 hidden"></div>
        <div class="wdt-ai-qc-explanation text-muted m-b-8" style="white-space:pre-wrap;"></div>

        <h4 class="c-title-color m-b-2 f-14">
            <?php esc_html_e('Suggested structure', 'wpdatatables'); ?>
        </h4>
        <div class="form-group m-b-10">
            <div class="fg-line">
                <textarea class="form-control wdt-ai-qc-summary" rows="4"
                          style="font-family:monospace;font-size:12px;" readonly></textarea>
            </div>
        </div>

        <div class="wdt-ai-qc-sql-wrap hidden">
            <h4 class="c-title-color m-b-2 f-14">
                <?php esc_html_e('Suggested SQL', 'wpdatatables'); ?>
            </h4>
            <div class="form-group m-b-10">
                <div class="fg-line">
                    <textarea class="form-control wdt-ai-qc-sql" rows="3"
                              style="font-family:monospace;" readonly></textarea>
                </div>
            </div>
        </div>

        <button type="button" class="btn btn-primary wdt-ai-qc-apply">
            <?php esc_html_e('Apply to wizard', 'wpdatatables'); ?>
        </button>
    </div>
</div>
