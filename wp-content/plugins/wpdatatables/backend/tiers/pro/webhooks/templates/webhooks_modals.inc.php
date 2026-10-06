<?php defined('ABSPATH') or die('Access denied.'); ?>

<?php
use WDTIntegration\Webhooks\WebhookManagerService;

$eventLabels = WebhookManagerService::eventLabels();
$methods = WebhookManagerService::ALLOWED_METHODS;
$formats = WebhookManagerService::ALLOWED_FORMATS;
?>

<!-- Add/Edit Webhook Modal -->
<div class="modal fade wpdt-c" id="wdt-webhook-modal" data-backdrop="static" data-keyboard="false" tabindex="-1"
     role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php include WDT_TEMPLATE_PATH . 'admin/common/preloader.inc.php'; ?>

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="wpdt-icon-times-full"></i></span>
                </button>
                <h4 class="modal-title" id="wdt-webhook-modal-title">
                    <?php esc_html_e('Add webhook', 'wpdatatables'); ?>
                </h4>
            </div>

            <div class="modal-body">
                <input type="hidden" id="wdt-webhook-id" value="">

                <div class="row">
                    <div class="col-sm-12">
                        <h5 class="c-black m-b-10"><?php esc_html_e('Name', 'wpdatatables'); ?></h5>
                        <div class="form-group">
                            <div class="fg-line">
                                <input type="text" class="form-control input-sm" id="wdt-webhook-name"
                                       placeholder="<?php esc_attr_e('My webhook', 'wpdatatables'); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <h5 class="c-black m-b-10"><?php esc_html_e('URL', 'wpdatatables'); ?></h5>
                        <div class="form-group">
                            <div class="fg-line">
                                <input type="url" class="form-control input-sm" id="wdt-webhook-url"
                                       placeholder="https://example.com/webhook">
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <h5 class="c-black m-b-10"><?php esc_html_e('Event', 'wpdatatables'); ?></h5>
                        <div class="form-group">
                            <div class="fg-line">
                                <div class="select">
                                    <select class="selectpicker" id="wdt-webhook-event">
                                        <?php foreach ($eventLabels as $slug => $label) : ?>
                                            <option value="<?php echo esc_attr($slug); ?>">
                                                <?php echo esc_html($label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <h5 class="c-black m-b-10"><?php esc_html_e('Method', 'wpdatatables'); ?></h5>
                        <div class="form-group">
                            <div class="fg-line">
                                <div class="select">
                                    <select class="selectpicker" id="wdt-webhook-method">
                                        <?php foreach ($methods as $method) : ?>
                                            <option value="<?php echo esc_attr($method); ?>" <?php selected($method, 'POST'); ?>>
                                                <?php echo esc_html($method); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <h5 class="c-black m-b-10"><?php esc_html_e('Format', 'wpdatatables'); ?></h5>
                        <div class="form-group">
                            <div class="fg-line">
                                <div class="select">
                                    <select class="selectpicker" id="wdt-webhook-format">
                                        <?php foreach ($formats as $format) : ?>
                                            <option value="<?php echo esc_attr($format); ?>">
                                                <?php echo esc_html($format === 'form-data' ? 'Form data' : 'JSON'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <h5 class="c-black m-b-10">
                            <?php esc_html_e('Secret (optional)', 'wpdatatables'); ?>
                            <i class="wpdt-icon-info-circle-thin" data-toggle="tooltip" data-placement="right"
                               title="<?php esc_attr_e('Used to sign the body with HMAC-SHA256 in the X-WPDataTables-Signature header. Leave blank when editing to keep the current secret.', 'wpdatatables'); ?>"></i>
                        </h5>
                        <div class="form-group">
                            <div class="fg-line">
                                <input type="password" class="form-control input-sm" id="wdt-webhook-secret"
                                       autocomplete="new-password"
                                       placeholder="<?php esc_attr_e('Optional signing secret', 'wpdatatables'); ?>">
                            </div>
                            <small class="text-muted" id="wdt-webhook-secret-hint" style="display:none;">
                                <?php esc_html_e('A secret is already saved. Enter a new value to replace it.', 'wpdatatables'); ?>
                            </small>
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <h5 class="c-black m-b-10">
                            <?php esc_html_e('Custom headers (optional)', 'wpdatatables'); ?>
                        </h5>
                        <div id="wdt-webhook-headers-list"></div>
                        <button type="button" class="btn btn-default btn-xs m-t-5" id="wdt-webhook-add-header">
                            <i class="wpdt-icon-plus"></i>
                            <?php esc_html_e('Add header', 'wpdatatables'); ?>
                        </button>
                    </div>

                    <div class="col-sm-12 m-t-15">
                        <div class="toggle-switch" data-ts-color="blue">
                            <input id="wdt-webhook-enabled" type="checkbox" checked>
                            <label for="wdt-webhook-enabled" class="ts-label">
                                <?php esc_html_e('Enabled', 'wpdatatables'); ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <small class="text-danger form-general-error" id="wdt-webhook-form-error" style="display:none;"></small>
                <hr>
                <button type="button" class="btn btn-danger btn-icon-text" data-dismiss="modal">
                    <?php esc_html_e('Cancel', 'wpdatatables'); ?>
                </button>
                <button type="button" class="btn btn-primary btn-icon-text" id="wdt-webhook-submit">
                    <i class="wpdt-icon-save"></i>
                    <?php esc_html_e('Save', 'wpdatatables'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Webhook Modal -->
<div class="modal fade wpdt-c" id="wdt-delete-webhook-modal" data-backdrop="static" data-keyboard="false" tabindex="-1"
     role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="wpdt-icon-times-full"></i></span>
                </button>
                <h4 class="modal-title"><?php esc_html_e('Delete webhook', 'wpdatatables'); ?></h4>
            </div>
            <div class="modal-body">
                <p><?php esc_html_e('Are you sure you want to delete this webhook? This action cannot be undone.', 'wpdatatables'); ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-icon-text" data-dismiss="modal">
                    <?php esc_html_e('Cancel', 'wpdatatables'); ?>
                </button>
                <button type="button" class="btn btn-danger btn-icon-text" id="wdt-confirm-delete-webhook">
                    <i class="wpdt-icon-trash"></i>
                    <?php esc_html_e('Delete', 'wpdatatables'); ?>
                </button>
            </div>
        </div>
    </div>
</div>
