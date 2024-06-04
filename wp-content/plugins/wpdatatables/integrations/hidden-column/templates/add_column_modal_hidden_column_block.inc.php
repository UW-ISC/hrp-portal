<?php defined('ABSPATH') or die('Access denied.'); ?>

<div class="col-xs-12 wdt-add-hidden-default-value-block" hidden="hidden">
    <h5 class="c-black m-b-10">
        <?php esc_html_e('Dynamic editor predefined value', 'wpdatatables'); ?>
    </h5>
    <div class="form-group">
        <div class="fg-line">
            <select class="selectpicker wdt-add-hidden-default-value">
                <optgroup label="<?php esc_html_e('Current User', 'wpdatatables'); ?>">
                    <option value="user-id"><?php esc_html_e('Current User ID', 'wpdatatables'); ?></option>
                    <option value="user-display-name"><?php esc_html_e('Current User Display Name', 'wpdatatables'); ?></option>
                    <option value="user-first-name"><?php esc_html_e('Current User First Name', 'wpdatatables'); ?></option>
                    <option value="user-last-name"><?php esc_html_e('Current User Last Name', 'wpdatatables'); ?></option>
                    <option value="user-email"><?php esc_html_e('Current User Email', 'wpdatatables'); ?></option>
                    <option value="user-login"><?php esc_html_e('Current User Login', 'wpdatatables'); ?></option>
                    <option value="user-ip"><?php esc_html_e('Current User IP Address', 'wpdatatables'); ?></option>
                </optgroup>
                <optgroup label="<?php esc_html_e('Current Date/Time', 'wpdatatables'); ?>">
                    <option value="date"><?php esc_html_e('Current Date', 'wpdatatables'); ?></option>
                    <option value="datetime"><?php esc_html_e('Current Datetime', 'wpdatatables'); ?></option>
                    <option value="time"><?php esc_html_e('Current Time', 'wpdatatables'); ?></option>
                </optgroup>
                <optgroup label="<?php esc_html_e('Placeholders', 'wpdatatables'); ?>">
                    <option value="p-var1"><?php esc_html_e('Placeholder %VAR1%', 'wpdatatables'); ?></option>
                    <option value="p-var2"><?php esc_html_e('Placeholder %VAR2%', 'wpdatatables'); ?></option>
                    <option value="p-var3"><?php esc_html_e('Placeholder %VAR3%', 'wpdatatables'); ?></option>
                    <option value="p-var4"><?php esc_html_e('Placeholder %VAR4%', 'wpdatatables'); ?></option>
                    <option value="p-var5"><?php esc_html_e('Placeholder %VAR5%', 'wpdatatables'); ?></option>
                    <option value="p-var6"><?php esc_html_e('Placeholder %VAR6%', 'wpdatatables'); ?></option>
                    <option value="p-var7"><?php esc_html_e('Placeholder %VAR7%', 'wpdatatables'); ?></option>
                    <option value="p-var8"><?php esc_html_e('Placeholder %VAR8%', 'wpdatatables'); ?></option>
                    <option value="p-var9"><?php esc_html_e('Placeholder %VAR9%', 'wpdatatables'); ?></option>
                </optgroup>
                <optgroup label="<?php esc_html_e('Post Data', 'wpdatatables'); ?>">
                    <option value="post-id"><?php esc_html_e('Post ID', 'wpdatatables'); ?></option>
                    <option value="post-title"><?php esc_html_e('Post Title', 'wpdatatables'); ?></option>
                    <option value="post-author"><?php esc_html_e('Post Author ID', 'wpdatatables'); ?></option>
                    <option value="post-type"><?php esc_html_e('Post Type', 'wpdatatables'); ?></option>
                    <option value="post-status"><?php esc_html_e('Post Status', 'wpdatatables'); ?></option>
                    <option value="post-parent"><?php esc_html_e('Post Parent ID', 'wpdatatables'); ?></option>
                    <option value="post-url"><?php esc_html_e('Post URL', 'wpdatatables'); ?></option>
                    <option value="post-meta"><?php esc_html_e('Post Meta Value as string', 'wpdatatables'); ?></option>
                    <option value="acf-data"><?php esc_html_e('ACF Data', 'wpdatatables'); ?></option>
                </optgroup>
                <optgroup label="<?php esc_html_e('HTTP Data', 'wpdatatables'); ?>">
                    <option value="user-agent"><?php esc_html_e('HTTP User Agent', 'wpdatatables'); ?></option>
                    <option value="refer-url"><?php esc_html_e('HTTP Refer URL', 'wpdatatables'); ?></option>
                    <option value="query-param"><?php esc_html_e('Query Parameter (GET)', 'wpdatatables'); ?></option>
                </optgroup>
            </select>
        </div>
    </div>
</div>

<div class="col-xs-12 wdt-add-hidden-query-param-value-block" hidden="hidden">
    <h5 class="c-black m-b-10">
        <?php esc_html_e('Query Parameter Key', 'wpdatatables'); ?>
    </h5>
    <div class="form-group">
        <div class="fg-line">
            <input type="text" class="form-control input-sm wdt-add-hidden-query-param-value"
                   placeholder="<?php esc_html_e('E.g query_parameter_key', 'wpdatatables'); ?>">
        </div>
    </div>
</div>

<div class="col-xs-12 wdt-add-hidden-post-meta-value-block" hidden="hidden">
    <h5 class="c-black m-b-10">
        <?php esc_html_e('Post Meta Key', 'wpdatatables'); ?>
    </h5>
    <div class="form-group">
        <div class="fg-line">
            <input type="text" class="form-control input-sm wdt-add-hidden-post-meta-value"
                   placeholder="<?php esc_html_e('E.g meta_key', 'wpdatatables'); ?>">
        </div>
    </div>
</div>

<div class="col-xs-12 wdt-add-hidden-acf-data-value-block" hidden="hidden">
    <h5 class="c-black m-b-10">
        <?php esc_html_e('ACF Key', 'wpdatatables'); ?>
    </h5>
    <div class="form-group">
        <div class="fg-line">
            <input type="text" class="form-control input-sm wdt-add-hidden-acf-data-value"
                   placeholder="<?php esc_html_e('E.g acf_key', 'wpdatatables'); ?>">
        </div>
    </div>
</div>
