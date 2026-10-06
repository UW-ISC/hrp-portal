<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php
/**
 * AI SQL Assistant panel — Feature 3 (SQL-linked tables).
 *
 * Renders as the right column beside `#wdt-mysql-query` (same row split as
 * Input data source type / Server-side processing above). Bound by
 * assets/js/ai/wdt-ai-query-assistant.js.
 *
 * @var bool   $wdt_ai_available
 * @var array  $wdt_ai_models
 * @var string $wdt_ai_default_model
 */

if (empty($wdt_ai_available)) {
    return;
}

$wdt_ai_qa_tooltip = __(
    'Describe the table you need, fix a failing query, or ask for improvements. Review the SQL before applying.',
    'wpdatatables'
);
?>

<div class="col-sm-6 wdt-ai-sql-assistant-col">
    <div class="wdt-ai-query-assistant wdt-ai-assistant-panel wdt-ai-assistant-panel--side">
        <h4 class="c-title-color m-b-2 f-14">
            <?php esc_html_e('AI SQL Assistant', 'wpdatatables'); ?>
            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
               title="<?php echo esc_attr($wdt_ai_qa_tooltip); ?>"></i>
        </h4>

        <div class="form-group m-b-10">
            <div class="fg-line">
                <textarea class="form-control wdt-ai-qa-prompt" rows="2"
                          placeholder="<?php esc_attr_e('e.g. Create a table of all WP posts that have comments', 'wpdatatables'); ?>"></textarea>
            </div>
        </div>

        <div class="wdt-ai-assistant-controls wdt-ai-assistant-controls--stacked">
            <div class="wdt-ai-assistant-control wdt-ai-assistant-control--mode">
                <h4 class="c-title-color m-b-2 f-14">
                    <?php esc_html_e('Mode', 'wpdatatables'); ?>
                    <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                       title="<?php esc_attr_e('Generate a new query, fix errors, improve an existing query, or explain what it does.', 'wpdatatables'); ?>"></i>
                </h4>
                <div class="form-group m-b-0">
                    <select class="selectpicker wdt-ai-dropdown wdt-ai-qa-mode" id="wdt-ai-qa-mode" data-width="100%">
                        <option value="generate"><?php esc_html_e('Generate', 'wpdatatables'); ?></option>
                        <option value="fix"><?php esc_html_e('Fix errors', 'wpdatatables'); ?></option>
                        <option value="improve"><?php esc_html_e('Improve', 'wpdatatables'); ?></option>
                        <option value="explain"><?php esc_html_e('Explain', 'wpdatatables'); ?></option>
                    </select>
                </div>
            </div>

            <?php if (!empty($wdt_ai_models)) : ?>
                <div class="wdt-ai-assistant-control wdt-ai-assistant-control--model">
                    <h4 class="c-title-color m-b-2 f-14">
                        <?php esc_html_e('Model', 'wpdatatables'); ?>
                        <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                           title="<?php esc_attr_e('AI model used for generation. Only models configured in WordPress AI settings are listed.', 'wpdatatables'); ?>"></i>
                    </h4>
                    <div class="form-group m-b-0">
                        <select class="selectpicker wdt-ai-dropdown wdt-ai-qa-model" id="wdt-ai-qa-model" data-width="100%">
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
                <button type="button" class="btn btn-primary wdt-ai-qa-run">
                    <?php esc_html_e('Ask AI', 'wpdatatables'); ?>
                </button>
                <span class="wdt-ai-qa-status text-muted"></span>
            </div>
        </div>

        <div class="wdt-ai-qa-error-wrap form-group m-t-10 hidden">
            <h4 class="c-title-color m-b-2 f-14">
                <?php esc_html_e('Error message', 'wpdatatables'); ?>
                <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                   title="<?php esc_attr_e('Optional. Paste the SQL / database error to help the AI fix the query.', 'wpdatatables'); ?>"></i>
            </h4>
            <div class="fg-line">
                <textarea class="form-control wdt-ai-qa-error" rows="2"
                          placeholder="<?php esc_attr_e('Paste the SQL / database error message (optional)', 'wpdatatables'); ?>"></textarea>
            </div>
        </div>

        <div class="wdt-ai-qa-result hidden m-t-15">
            <div class="wdt-ai-qa-warnings text-danger m-b-8 hidden"></div>
            <div class="wdt-ai-qa-explanation text-muted m-b-8" style="white-space:pre-wrap;"></div>

            <h4 class="c-title-color m-b-2 f-14">
                <?php esc_html_e('Suggested SQL', 'wpdatatables'); ?>
            </h4>
            <div class="form-group m-b-10">
                <div class="fg-line">
                    <textarea class="form-control wdt-ai-qa-sql" rows="4" style="font-family:monospace;"></textarea>
                </div>
            </div>

            <button type="button" class="btn btn-primary wdt-ai-qa-apply">
                <?php esc_html_e('Apply to editor', 'wpdatatables'); ?>
            </button>
        </div>
    </div>
</div>
