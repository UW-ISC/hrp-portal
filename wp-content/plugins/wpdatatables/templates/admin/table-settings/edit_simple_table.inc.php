<?php defined('ABSPATH') or die('Access denied.'); ?>

<div class="wrap wdt-datatables-admin-wrap">

    <?php wp_nonce_field('wdtEditNonce', 'wdtNonce'); ?>

    <?php do_action('wpdatatables_admin_before_edit_simple_table'); ?>
    <div class="container">
        <div class="row">

            <?php include WDT_TEMPLATE_PATH . 'admin/table-settings/simple_table_settings_block.inc.php'; ?>

        </div>
        <!-- /.row -->

        <div class="row">

            <?php include WDT_TEMPLATE_PATH . 'admin/table-settings/simple_table_preview_block.inc.php'; ?>

        </div>
        <!-- /.row-->

    </div>
    <!-- /.container -->


    <!-- Error message modal -->
    <?php include WDT_TEMPLATE_PATH . 'admin/common/error_modal.inc.php'; ?>
    <!-- /Error message modal -->

    <!-- Close modal -->
    <?php include WDT_TEMPLATE_PATH . 'admin/common/close_modal.inc.php'; ?>
    <!-- Close modal -->

    <!-- Warning message modal -->
    <?php include WDT_TEMPLATE_PATH . 'admin/common/warningModal.inc.php'; ?>
    <!-- /Warning message modal -->

    <!-- Link modal -->
    <?php include WDT_TEMPLATE_PATH . 'admin/common/linkModal.inc.php'; ?>
    <!-- /Link modal -->

    <!-- Shortcode modal -->
    <?php include WDT_TEMPLATE_PATH . 'admin/common/shortcodeModal.inc.php'; ?>
    <!-- /Shortcode modal -->

    <!-- HTML modal -->
    <?php include WDT_TEMPLATE_PATH . 'admin/common/HTMLModal.inc.php'; ?>
    <!-- /HTML modal -->

    <!-- Star modal -->
    <?php include WDT_TEMPLATE_PATH . 'admin/common/starModal.inc.php'; ?>
    <!-- /Star modal -->

    <!-- /Modals -->
    <script type='text/javascript' src='<?php echo site_url(); ?>/wp-includes/js/tinymce/tinymce.min.js'></script>
    <script type="text/javascript">tinymce.PluginManager.load('code', <?php echo "'" . WDT_JS_PATH . 'editor-plugins/code/plugin.min.js' . "'"; ?>)</script>
    <style>
        .mce-container {
            z-index: 200000 !important
        }

        .mce-container label {
            max-width: none !important
        }

        .mce-tinymce {
            box-shadow: none
        }

        .mce-container, .mce-container *, .mce-widget, .mce-widget * {
            color: inherit;
            font-family: inherit
        }

        .mce-container .mce-monospace, .mce-widget .mce-monospace {
            font-family: Consolas, Monaco, monospace;
            font-size: 13px;
            line-height: 150%
        }

        #mce-modal-block, #mce-modal-block.mce-fade {
            opacity: .7;
            transition: none;
            background: #000
        }

        .mce-window {
            border-radius: 0;
            box-shadow: 0 3px 6px rgba(0, 0, 0, .3);
            -webkit-font-smoothing: subpixel-antialiased;
            transition: none
        }

        .mce-window .mce-container-body.mce-abs-layout {
            overflow: visible
        }

        .mce-window .mce-window-head {
            background: #fcfcfc;
            border-bottom: 1px solid #ddd;
            padding: 0;
            min-height: 36px
        }

        .mce-window .mce-window-head .mce-title {
            color: #444;
            font-size: 18px;
            font-weight: 600;
            line-height: 36px;
            margin: 0;
            padding: 0 36px 0 16px
        }

        .mce-window .mce-window-head .mce-close, .mce-window-head .mce-close .mce-i-remove {
            color: transparent;
            top: 0;
            right: 0;
            width: 36px;
            height: 36px;
            padding: 0;
            line-height: 36px;
            text-align: center
        }

        .mce-window-head .mce-close .mce-i-remove:before {
            font: normal 20px/36px dashicons;
            text-align: center;
            color: #666;
            width: 36px;
            height: 36px;
            display: block
        }

        .mce-window-head .mce-close:focus .mce-i-remove:before, .mce-window-head .mce-close:hover .mce-i-remove:before {
            color: #00a0d2
        }

        .mce-window-head .mce-close:focus .mce-i-remove, div.mce-tab:focus {
            box-shadow: 0 0 0 1px #5b9dd9, 0 0 2px 1px rgba(30, 140, 190, .8)
        }

        .mce-window .mce-window-head .mce-dragh {
            width: calc(100% - 36px)
        }

        .mce-window .mce-foot {
            border-top: 1px solid #ddd
        }

        #wp-link .query-results, .mce-checkbox i.mce-i-checkbox, .mce-textbox {
            border: 1px solid #ddd;
            border-radius: 0;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, .07);
            transition: .05s all ease-in-out
        }

        #wp-link .query-results:focus, .mce-checkbox:focus i.mce-i-checkbox, .mce-textbox.mce-focus, .mce-textbox:focus {
            border-color: #5b9dd9;
            box-shadow: 0 0 2px rgba(30, 140, 190, .8)
        }

        .mce-window .mce-wp-help {
            height: 360px;
            width: 460px;
            overflow: auto
        }

        .mce-window .mce-wp-help * {
            box-sizing: border-box
        }

        .mce-window .mce-wp-help > .mce-container-body {
            width: auto !important
        }

        .mce-window .wp-editor-help {
            padding: 10px 10px 0 20px
        }

        .mce-window .wp-editor-help h2, .mce-window .wp-editor-help p {
            margin: 8px 0;
            white-space: normal;
            font-size: 14px;
            font-weight: 400
        }

        .mce-window .wp-editor-help table {
            width: 100%;
            margin-bottom: 20px
        }

        .mce-window .wp-editor-help table.wp-help-single {
            margin: 0 8px 20px
        }

        .mce-window .wp-editor-help table.fixed {
            table-layout: fixed
        }

        .mce-window .wp-editor-help table.fixed td:nth-child(odd), .mce-window .wp-editor-help table.fixed th:nth-child(odd) {
            width: 12%
        }

        .mce-window .wp-editor-help table.fixed td:nth-child(even), .mce-window .wp-editor-help table.fixed th:nth-child(even) {
            width: 38%
        }

        .mce-window .wp-editor-help table.fixed th:nth-child(odd) {
            padding: 5px 0 0
        }

        .mce-window .wp-editor-help td, .mce-window .wp-editor-help th {
            font-size: 13px;
            padding: 5px;
            vertical-align: middle;
            word-wrap: break-word;
            white-space: normal
        }

        .mce-window .wp-editor-help th {
            font-weight: 600;
            padding-bottom: 0
        }

        .mce-window .wp-editor-help kbd {
            font-family: monospace;
            padding: 2px 7px 3px;
            font-weight: 600;
            margin: 0;
            background: #eaeaea;
            background: rgba(0, 0, 0, .08)
        }

        .mce-window .wp-help-th-center td:nth-child(odd), .mce-window .wp-help-th-center th:nth-child(odd) {
            text-align: center
        }

        .mce-floatpanel.mce-popover, .mce-menu {
            border-color: rgba(0, 0, 0, .15);
            border-radius: 0;
            box-shadow: 0 3px 5px rgba(0, 0, 0, .2)
        }

        .mce-floatpanel.mce-popover.mce-bottom, .mce-menu {
            margin-top: 2px
        }

        .mce-floatpanel .mce-arrow {
            display: none
        }

        .mce-menu .mce-container-body {
            min-width: 160px
        }

        .mce-menu-item {
            border: none;
            margin-bottom: 2px;
            padding: 6px 15px 6px 12px
        }

        .mce-menu-has-icons i.mce-ico {
            line-height: 20px
        }

        div.mce-panel {
            border: 0;
            background: #fff
        }

        .mce-panel.mce-menu {
            border: 1px solid #ddd
        }

        div.mce-tab {
            line-height: 13px
        }

        div.mce-toolbar-grp {
            border-bottom: 1px solid #ddd;
            background: #f5f5f5;
            padding: 0;
            position: relative
        }

        div.mce-inline-toolbar-grp {
            border: 1px solid #a0a5aa;
            border-radius: 2px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .15);
            box-sizing: border-box;
            margin-bottom: 8px;
            position: absolute;
            -moz-user-select: none;
            -webkit-user-select: none;
            -ms-user-select: none;
            user-select: none;
            max-width: 98%;
            z-index: 100100
        }

        div.mce-inline-toolbar-grp > div.mce-stack-layout {
            padding: 1px
        }

        div.mce-inline-toolbar-grp.mce-arrow-up {
            margin-bottom: 0;
            margin-top: 8px
        }

        div.mce-inline-toolbar-grp:after, div.mce-inline-toolbar-grp:before {
            position: absolute;
            left: 50%;
            display: block;
            width: 0;
            height: 0;
            border-style: solid;
            border-color: transparent;
            content: ""
        }

        div.mce-inline-toolbar-grp.mce-arrow-up:before {
            top: -9px;
            border-bottom-color: #a0a5aa;
            border-width: 0 9px 9px;
            margin-left: -9px
        }

        div.mce-inline-toolbar-grp.mce-arrow-down:before {
            bottom: -9px;
            border-top-color: #a0a5aa;
            border-width: 9px 9px 0;
            margin-left: -9px
        }

        div.mce-inline-toolbar-grp.mce-arrow-up:after {
            top: -8px;
            border-bottom-color: #f5f5f5;
            border-width: 0 8px 8px;
            margin-left: -8px
        }

        div.mce-inline-toolbar-grp.mce-arrow-down:after {
            bottom: -8px;
            border-top-color: #f5f5f5;
            border-width: 8px 8px 0;
            margin-left: -8px
        }

        div.mce-inline-toolbar-grp.mce-arrow-left:after, div.mce-inline-toolbar-grp.mce-arrow-left:before {
            margin: 0
        }

        div.mce-inline-toolbar-grp.mce-arrow-left:before {
            left: 20px
        }

        div.mce-inline-toolbar-grp.mce-arrow-left:after {
            left: 21px
        }

        div.mce-inline-toolbar-grp.mce-arrow-right:after, div.mce-inline-toolbar-grp.mce-arrow-right:before {
            left: auto;
            margin: 0
        }

        div.mce-inline-toolbar-grp.mce-arrow-right:before {
            right: 20px
        }

        div.mce-inline-toolbar-grp.mce-arrow-right:after {
            right: 21px
        }

        div.mce-inline-toolbar-grp.mce-arrow-full {
            right: 0
        }

        div.mce-inline-toolbar-grp.mce-arrow-full > div {
            width: 100%;
            overflow-x: auto
        }

        div.mce-toolbar-grp > div {
            padding: 3px
        }

        .has-dfw div.mce-toolbar-grp .mce-toolbar.mce-first {
            padding-right: 32px
        }

        .mce-toolbar .mce-btn-group {
            margin: 0
        }

        .block-library-classic__toolbar .mce-toolbar-grp .mce-toolbar:not(:first-child) {
            display: none
        }

        .block-library-classic__toolbar.has-advanced-toolbar .mce-toolbar-grp .mce-toolbar {
            display: block
        }

        div.mce-statusbar {
            border-top: 1px solid #e5e5e5
        }

        div.mce-path {
            padding: 2px 10px;
            margin: 0
        }

        .mce-path, .mce-path .mce-divider, .mce-path-item {
            font-size: 12px
        }

        .mce-toolbar .mce-btn, .qt-dfw {
            border-color: transparent;
            background: 0 0;
            box-shadow: none;
            text-shadow: none;
            cursor: pointer
        }

        .mce-btn .mce-txt {
            direction: inherit;
            text-align: inherit
        }

        .mce-toolbar .mce-btn-group .mce-btn, .qt-dfw {
            border: 1px solid transparent;
            margin: 2px;
            border-radius: 2px
        }

        .mce-toolbar .mce-btn-group .mce-btn:focus, .mce-toolbar .mce-btn-group .mce-btn:hover, .qt-dfw:focus, .qt-dfw:hover {
            background: #fafafa;
            border-color: #555d66;
            color: #23282d;
            box-shadow: inset 0 1px 0 #fff, 0 1px 0 rgba(0, 0, 0, .08);
            outline: 0
        }

        .mce-toolbar .mce-btn-group .mce-btn.mce-active, .mce-toolbar .mce-btn-group .mce-btn:active, .qt-dfw.active {
            background: #ebebeb;
            border-color: #555d66;
            box-shadow: inset 0 2px 5px -3px rgba(0, 0, 0, .3)
        }

        .mce-btn.mce-active, .mce-btn.mce-active button, .mce-btn.mce-active i, .mce-btn.mce-active:hover button, .mce-btn.mce-active:hover i {
            color: inherit
        }

        .mce-toolbar .mce-btn-group .mce-btn.mce-active:focus, .mce-toolbar .mce-btn-group .mce-btn.mce-active:hover {
            border-color: #23282d
        }

        .mce-toolbar .mce-btn-group .mce-btn.mce-disabled:focus, .mce-toolbar .mce-btn-group .mce-btn.mce-disabled:hover {
            color: #a0a5aa;
            background: 0 0;
            border-color: #ddd;
            text-shadow: 0 1px 0 #fff;
            box-shadow: none
        }

        .mce-toolbar .mce-btn-group .mce-btn.mce-disabled:focus {
            border-color: #555d66
        }

        .mce-toolbar .mce-btn-group .mce-first, .mce-toolbar .mce-btn-group .mce-last {
            border-color: transparent
        }

        .mce-toolbar .mce-btn button, .qt-dfw {
            padding: 2px 3px;
            line-height: normal
        }

        .mce-toolbar .mce-listbox button {
            font-size: 13px;
            line-height: 20px;
            padding-left: 6px;
            padding-right: 20px
        }

        .mce-toolbar .mce-btn i {
            text-shadow: none
        }

        .mce-toolbar .mce-btn-group > div {
            white-space: normal
        }

        .mce-toolbar .mce-colorbutton .mce-open {
            border-right: 0
        }

        .mce-toolbar .mce-colorbutton .mce-preview {
            margin: 0;
            padding: 0;
            top: auto;
            bottom: 2px;
            left: 3px;
            height: 3px;
            width: 20px;
            background: #555d66
        }

        .mce-toolbar .mce-btn-group .mce-btn.mce-primary {
            min-width: 0;
            background: #0085ba;
            border-color: #0073aa #006799 #006799;
            box-shadow: 0 1px 0 #006799;
            color: #fff;
            text-decoration: none;
            text-shadow: none
        }

        .mce-toolbar .mce-btn-group .mce-btn.mce-primary button {
            padding: 2px 3px 1px
        }

        .mce-toolbar .mce-btn-group .mce-btn.mce-primary .mce-ico {
            color: #fff
        }

        .mce-toolbar .mce-btn-group .mce-btn.mce-primary:focus, .mce-toolbar .mce-btn-group .mce-btn.mce-primary:hover {
            background: #008ec2;
            border-color: #006799;
            color: #fff
        }

        .mce-toolbar .mce-btn-group .mce-btn.mce-primary:focus {
            box-shadow: 0 0 1px 1px #33b3db
        }

        .mce-toolbar .mce-btn-group .mce-btn.mce-primary:active {
            background: #0073aa;
            border-color: #006799;
            box-shadow: inset 0 2px 0 #006799
        }

        .mce-toolbar .mce-btn-group .mce-btn.mce-listbox {
            border-radius: 0;
            direction: ltr;
            background: #fff;
            border: 1px solid #ddd;
            box-shadow: inset 0 1px 1px -1px rgba(0, 0, 0, .2)
        }

        .mce-toolbar .mce-btn-group .mce-btn.mce-listbox:focus, .mce-toolbar .mce-btn-group .mce-btn.mce-listbox:hover {
            border-color: #b4b9be
        }

        .mce-panel .mce-btn i.mce-caret {
            border-top: 6px solid #555d66;
            margin-left: 2px;
            margin-right: 2px
        }

        .mce-listbox i.mce-caret {
            right: 4px
        }

        .mce-panel .mce-btn:focus i.mce-caret, .mce-panel .mce-btn:hover i.mce-caret {
            border-top-color: #23282d
        }

        .mce-panel .mce-active i.mce-caret {
            border-top: 0;
            border-bottom: 6px solid #23282d;
            margin-top: 7px
        }

        .mce-listbox.mce-active i.mce-caret {
            margin-top: -3px
        }

        .mce-toolbar .mce-splitbtn:hover .mce-open {
            border-right-color: transparent
        }

        .mce-toolbar .mce-splitbtn .mce-open.mce-active {
            background: 0 0;
            outline: 0
        }

        .mce-menu .mce-menu-item.mce-active.mce-menu-item-normal, .mce-menu .mce-menu-item.mce-active.mce-menu-item-preview, .mce-menu .mce-menu-item.mce-selected, .mce-menu .mce-menu-item:focus, .mce-menu .mce-menu-item:hover {
            background: #0073aa;
            color: #fff
        }

        .mce-menu .mce-menu-item.mce-selected .mce-caret, .mce-menu .mce-menu-item:focus .mce-caret, .mce-menu .mce-menu-item:hover .mce-caret {
            border-left-color: #fff
        }

        .rtl .mce-menu .mce-menu-item.mce-selected .mce-caret, .rtl .mce-menu .mce-menu-item:focus .mce-caret, .rtl .mce-menu .mce-menu-item:hover .mce-caret {
            border-left-color: inherit;
            border-right-color: #fff
        }

        .mce-menu .mce-menu-item.mce-active .mce-menu-shortcut, .mce-menu .mce-menu-item.mce-disabled:hover .mce-ico, .mce-menu .mce-menu-item.mce-disabled:hover .mce-text, .mce-menu .mce-menu-item.mce-selected .mce-ico, .mce-menu .mce-menu-item.mce-selected .mce-text, .mce-menu .mce-menu-item:focus .mce-ico, .mce-menu .mce-menu-item:focus .mce-menu-shortcut, .mce-menu .mce-menu-item:focus .mce-text, .mce-menu .mce-menu-item:hover .mce-ico, .mce-menu .mce-menu-item:hover .mce-menu-shortcut, .mce-menu .mce-menu-item:hover .mce-text {
            color: inherit
        }

        .mce-menu .mce-menu-item.mce-disabled {
            cursor: default
        }

        .mce-menu .mce-menu-item.mce-disabled:hover {
            background: #ccc
        }

        div.mce-menubar {
            border-color: #e5e5e5;
            background: #fff;
            border-width: 0 0 1px
        }

        .mce-menubar .mce-menubtn.mce-active, .mce-menubar .mce-menubtn:focus, .mce-menubar .mce-menubtn:hover {
            border-color: transparent;
            background: 0 0
        }

        .mce-menubar .mce-menubtn:focus {
            color: #124964;
            box-shadow: 0 0 0 1px #5b9dd9, 0 0 2px 1px rgba(30, 140, 190, .8)
        }

        .mce-menu-item-sep:hover, div.mce-menu .mce-menu-item-sep {
            border-bottom: 1px solid #ddd;
            height: 0;
            margin: 5px 0
        }

        .mce-menubtn span {
            margin-right: 0;
            padding-left: 3px
        }

        .mce-menu-has-icons i.mce-ico:before {
            margin-left: -2px
        }

        .mce-menu.mce-menu-align .mce-menu-item-normal {
            position: relative
        }

        .mce-menu.mce-menu-align .mce-menu-shortcut {
            bottom: .6em;
            font-size: .9em
        }

        .mce-primary button, .mce-primary button i {
            text-align: center;
            color: #fff;
            text-shadow: none;
            padding: 0;
            line-height: 26px
        }

        .mce-window .mce-btn {
            color: #555;
            background: #f7f7f7;
            text-decoration: none;
            font-size: 13px;
            line-height: 26px;
            height: 28px;
            margin: 0;
            padding: 0;
            cursor: pointer;
            border: 1px solid #ccc;
            -webkit-appearance: none;
            border-radius: 3px;
            white-space: nowrap;
            box-shadow: 0 1px 0 #ccc
        }

        .mce-window .mce-btn::-moz-focus-inner {
            border-width: 0;
            border-style: none;
            padding: 0
        }

        .mce-window .mce-btn:focus, .mce-window .mce-btn:hover {
            background: #fafafa;
            border-color: #999;
            color: #23282d
        }

        .mce-window .mce-btn:focus {
            border-color: #5b9dd9;
            box-shadow: 0 0 3px rgba(0, 115, 170, .8)
        }

        .mce-window .mce-btn:active {
            background: #eee;
            border-color: #999;
            box-shadow: inset 0 2px 5px -3px rgba(0, 0, 0, .5);
            transform: translateY(1px)
        }

        .mce-window .mce-btn.mce-disabled {
            color: #a0a5aa !important;
            border-color: #ddd !important;
            background: #f7f7f7 !important;
            box-shadow: none !important;
            text-shadow: 0 1px 0 #fff !important;
            cursor: default;
            transform: none !important
        }

        .mce-window .mce-btn.mce-primary {
            background: #0085ba;
            border-color: #0073aa #006799 #006799;
            box-shadow: 0 1px 0 #006799;
            color: #fff;
            text-decoration: none;
            text-shadow: 0 -1px 1px #006799, 1px 0 1px #006799, 0 1px 1px #006799, -1px 0 1px #006799
        }

        .mce-window .mce-btn.mce-primary:focus, .mce-window .mce-btn.mce-primary:hover {
            background: #008ec2;
            border-color: #006799;
            color: #fff
        }

        .mce-window .mce-btn.mce-primary:focus {
            box-shadow: 0 1px 0 #0073aa, 0 0 2px 1px #33b3db
        }

        .mce-window .mce-btn.mce-primary:active {
            background: #0073aa;
            border-color: #006799;
            box-shadow: inset 0 2px 0 #006799;
            vertical-align: top
        }

        .mce-window .mce-btn.mce-primary.mce-disabled {
            color: #66c6e4 !important;
            background: #008ec2 !important;
            border-color: #007cb2 !important;
            box-shadow: none !important;
            text-shadow: 0 -1px 0 rgba(0, 0, 0, .1) !important;
            cursor: default
        }

        .mce-menubtn.mce-fixed-width span {
            overflow-x: hidden;
            text-overflow: ellipsis;
            width: 82px
        }

        .mce-charmap {
            margin: 3px
        }

        .mce-charmap td {
            padding: 0;
            border-color: #ddd;
            cursor: pointer
        }

        .mce-charmap td:hover {
            background: #f3f3f3
        }

        .mce-charmap td div {
            width: 18px;
            height: 22px;
            line-height: 22px
        }

        .mce-tooltip {
            margin-top: 2px
        }

        .rtl .mce-tooltip.wp-hide-mce-tooltip {
            display: none !important
        }

        .mce-tooltip-inner {
            border-radius: 3px;
            box-shadow: 0 3px 5px rgba(0, 0, 0, .2);
            color: #fff;
            font-size: 12px
        }

        .mce-ico {
            font-family: tinymce, Arial
        }

        .mce-btn-small .mce-ico {
            font-family: tinymce-small, Arial
        }

        .mce-toolbar .mce-ico {
            color: #555d66;
            line-height: 20px;
            width: 20px;
            height: 20px;
            text-align: center;
            text-shadow: none;
            margin: 0;
            padding: 0
        }

        .qt-dfw {
            color: #555d66;
            line-height: 20px;
            width: 28px;
            height: 26px;
            text-align: center;
            text-shadow: none
        }

        .mce-toolbar .mce-btn .mce-open {
            line-height: 20px
        }

        .mce-toolbar .mce-btn.mce-active .mce-open, .mce-toolbar .mce-btn:focus .mce-open, .mce-toolbar .mce-btn:hover .mce-open {
            border-left-color: #23282d
        }

        div.mce-notification {
            left: 10% !important;
            right: 10%
        }

        .mce-notification button.mce-close {
            right: 6px;
            top: 3px;
            font-weight: 400;
            color: #555d66
        }

        .mce-notification button.mce-close:focus, .mce-notification button.mce-close:hover {
            color: #000
        }

        i.mce-i-aligncenter, i.mce-i-alignjustify, i.mce-i-alignleft, i.mce-i-alignright, i.mce-i-backcolor, i.mce-i-blockquote, i.mce-i-bold, i.mce-i-bullist, i.mce-i-charmap, i.mce-i-dashicon, i.mce-i-dfw, i.mce-i-forecolor, i.mce-i-fullscreen, i.mce-i-help, i.mce-i-hr, i.mce-i-indent, i.mce-i-italic, i.mce-i-link, i.mce-i-ltr, i.mce-i-numlist, i.mce-i-outdent, i.mce-i-pastetext, i.mce-i-pasteword, i.mce-i-redo, i.mce-i-remove, i.mce-i-removeformat, i.mce-i-spellchecker, i.mce-i-strikethrough, i.mce-i-underline, i.mce-i-undo, i.mce-i-unlink, i.mce-i-wp-media-library, i.mce-i-wp_adv, i.mce-i-wp_code, i.mce-i-wp_fullscreen, i.mce-i-wp_help, i.mce-i-wp_more, i.mce-i-wp_page {
            font: normal 20px/1 dashicons;
            padding: 0;
            vertical-align: top;
            speak: none;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            margin-left: -2px;
            padding-right: 2px
        }

        .qt-dfw {
            font: normal 20px/1 dashicons;
            vertical-align: top;
            speak: none;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale
        }

        i.mce-i-bold:before {
            content: "\f200"
        }

        i.mce-i-italic:before {
            content: "\f201"
        }

        i.mce-i-bullist:before {
            content: "\f203"
        }

        i.mce-i-numlist:before {
            content: "\f204"
        }

        i.mce-i-blockquote:before {
            content: "\f205"
        }

        i.mce-i-alignleft:before {
            content: "\f206"
        }

        i.mce-i-aligncenter:before {
            content: "\f207"
        }

        i.mce-i-alignright:before {
            content: "\f208"
        }

        i.mce-i-link:before {
            content: "\f103"
        }

        i.mce-i-unlink:before {
            content: "\f225"
        }

        i.mce-i-wp_more:before {
            content: "\f209"
        }

        i.mce-i-strikethrough:before {
            content: "\f224"
        }

        i.mce-i-spellchecker:before {
            content: "\f210"
        }

        .qt-dfw:before, i.mce-i-dfw:before, i.mce-i-fullscreen:before, i.mce-i-wp_fullscreen:before {
            content: "\f211"
        }

        i.mce-i-wp_adv:before {
            content: "\f212"
        }

        i.mce-i-underline:before {
            content: "\f213"
        }

        i.mce-i-alignjustify:before {
            content: "\f214"
        }

        i.mce-i-backcolor:before, i.mce-i-forecolor:before {
            content: "\f215"
        }

        i.mce-i-pastetext:before {
            content: "\f217"
        }

        i.mce-i-removeformat:before {
            content: "\f218"
        }

        i.mce-i-charmap:before {
            content: "\f220"
        }

        i.mce-i-outdent:before {
            content: "\f221"
        }

        i.mce-i-indent:before {
            content: "\f222"
        }

        i.mce-i-undo:before {
            content: "\f171"
        }

        i.mce-i-redo:before {
            content: "\f172"
        }

        i.mce-i-help:before, i.mce-i-wp_help:before {
            content: "\f223"
        }

        i.mce-i-wp-media-library:before {
            content: "\f104"
        }

        i.mce-i-ltr:before {
            content: "\f320"
        }

        i.mce-i-wp_page:before {
            content: "\f105"
        }

        i.mce-i-hr:before {
            content: "\f460"
        }

        i.mce-i-remove:before {
            content: "\f158"
        }

        i.mce-i-wp_code:before {
            content: "\f475"
        }

        .rtl i.mce-i-outdent:before {
            content: "\f222"
        }

        .rtl i.mce-i-indent:before {
            content: "\f221"
        }

        .wp-editor-wrap {
            position: relative
        }

        .wp-editor-tools {
            position: relative;
            z-index: 1
        }

        .wp-editor-tools:after {
            clear: both;
            content: "";
            display: table
        }

        .wp-editor-container {
            clear: both;
            border: 1px solid #e5e5e5
        }

        .wp-editor-area {
            font-family: Consolas, Monaco, monospace;
            font-size: 13px;
            padding: 10px;
            margin: 1px 0 0;
            line-height: 150%;
            border: 0;
            outline: 0;
            display: block;
            resize: vertical;
            box-sizing: border-box
        }

        .rtl .wp-editor-area {
            font-family: Tahoma, Monaco, monospace
        }

        .locale-he-il .wp-editor-area {
            font-family: Arial, Monaco, monospace
        }

        .wp-editor-container textarea.wp-editor-area {
            width: 100%;
            margin: 0;
            box-shadow: none
        }

        .wp-editor-tabs {
            float: right
        }

        .wp-switch-editor {
            float: left;
            box-sizing: content-box;
            position: relative;
            top: 1px;
            background: #ebebeb;
            color: #666;
            cursor: pointer;
            font-size: 13px;
            line-height: 19px;
            height: 20px;
            margin: 5px 0 0 5px;
            padding: 3px 8px 4px;
            border: 1px solid #e5e5e5
        }

        .wp-switch-editor:focus {
            box-shadow: 0 0 0 1px #5b9dd9, 0 0 2px 1px rgba(30, 140, 190, .8);
            outline: 0;
            color: #23282d
        }

        .html-active .switch-html:focus, .tmce-active .switch-tmce:focus, .wp-switch-editor:active {
            box-shadow: none
        }

        .wp-switch-editor:active {
            background-color: #f5f5f5;
            box-shadow: none
        }

        .js .tmce-active .wp-editor-area {
            color: #fff
        }

        .tmce-active .quicktags-toolbar {
            display: none
        }

        .html-active .switch-html, .tmce-active .switch-tmce {
            background: #f5f5f5;
            color: #555;
            border-bottom-color: #f5f5f5
        }

        .wp-media-buttons {
            float: left
        }

        .wp-media-buttons .button {
            margin-right: 5px;
            margin-bottom: 4px;
            padding-left: 7px;
            padding-right: 7px
        }

        .wp-media-buttons .button:active {
            position: relative;
            top: 1px;
            margin-top: -1px;
            margin-bottom: 1px
        }

        .wp-media-buttons .insert-media {
            padding-left: 5px
        }

        .wp-media-buttons a {
            text-decoration: none;
            color: #444;
            font-size: 12px
        }

        .wp-media-buttons img {
            padding: 0 4px;
            vertical-align: middle
        }

        .wp-media-buttons span.wp-media-buttons-icon {
            display: inline-block;
            width: 18px;
            height: 18px;
            vertical-align: text-top;
            margin: 0 2px
        }

        .wp-media-buttons .add_media span.wp-media-buttons-icon {
            background: 0 0
        }

        .wp-media-buttons .add_media span.wp-media-buttons-icon:before {
            font: normal 18px/1 dashicons;
            speak: none;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale
        }

        .wp-media-buttons .add_media span.wp-media-buttons-icon:before {
            content: "\f104"
        }

        .mce-content-body dl.wp-caption {
            max-width: 100%
        }

        .quicktags-toolbar {
            padding: 3px;
            position: relative;
            border-bottom: 1px solid #ddd;
            background: #f5f5f5;
            min-height: 30px
        }

        .has-dfw .quicktags-toolbar {
            padding-right: 35px
        }

        .wp-core-ui .quicktags-toolbar input.button.button-small {
            margin: 2px
        }

        .quicktags-toolbar input[value=link] {
            text-decoration: underline
        }

        .quicktags-toolbar input[value=del] {
            text-decoration: line-through
        }

        .quicktags-toolbar input[value="i"] {
            font-style: italic
        }

        .quicktags-toolbar input[value="b"] {
            font-weight: 600
        }

        .mce-toolbar .mce-btn-group .mce-btn.mce-wp-dfw, .qt-dfw {
            position: absolute;
            top: 0;
            right: 0
        }

        .mce-toolbar .mce-btn-group .mce-btn.mce-wp-dfw {
            margin: 7px 7px 0 0
        }

        .qt-dfw {
            margin: 5px 5px 0 0
        }

        .qt-fullscreen {
            position: static;
            margin: 2px
        }

        @media screen and (max-width: 782px) {
            .mce-toolbar .mce-btn button, .qt-dfw {
                padding: 6px 7px
            }

            .mce-toolbar .mce-btn-group .mce-btn.mce-primary button {
                padding: 6px 7px 5px
            }

            .mce-toolbar .mce-btn-group .mce-btn {
                margin: 1px
            }

            .qt-dfw {
                width: 36px;
                height: 34px
            }

            .mce-toolbar .mce-btn-group .mce-btn.mce-wp-dfw {
                margin: 4px 4px 0 0
            }

            .mce-toolbar .mce-colorbutton .mce-preview {
                left: 8px;
                bottom: 6px
            }

            .mce-window .mce-btn {
                padding: 2px 0
            }

            .has-dfw .quicktags-toolbar, .has-dfw div.mce-toolbar-grp .mce-toolbar.mce-first {
                padding-right: 40px
            }
        }

        @media screen and (min-width: 782px) {
            .wp-core-ui .quicktags-toolbar input.button.button-small {
                font-size: 12px;
                height: 26px;
                line-height: 24px
            }
        }

        #wp_editbtns, #wp_gallerybtns {
            padding: 2px;
            position: absolute;
            display: none;
            z-index: 100020
        }

        #wp_delgallery, #wp_delimgbtn, #wp_editgallery, #wp_editimgbtn {
            border-color: #999;
            background-color: #eee;
            margin: 2px;
            padding: 2px;
            border-width: 1px;
            border-style: solid;
            border-radius: 3px
        }

        #wp_delgallery:hover, #wp_delimgbtn:hover, #wp_editgallery:hover, #wp_editimgbtn:hover {
            border-color: #555;
            background-color: #ccc
        }

        #wp-link-wrap {
            display: none;
            background-color: #fff;
            box-shadow: 0 3px 6px rgba(0, 0, 0, .3);
            width: 500px;
            overflow: hidden;
            margin-left: -250px;
            margin-top: -125px;
            position: fixed;
            top: 50%;
            left: 50%;
            z-index: 100105;
            transition: height .2s, margin-top .2s
        }

        #wp-link-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            min-height: 360px;
            background: #000;
            opacity: .7;
            z-index: 100100
        }

        #wp-link {
            position: relative;
            height: 100%
        }

        #wp-link-wrap {
            height: 500px;
            margin-top: -250px
        }

        #wp-link-wrap .wp-link-text-field {
            display: none
        }

        #wp-link-wrap.has-text-field .wp-link-text-field {
            display: block
        }

        #link-modal-title {
            background: #fcfcfc;
            border-bottom: 1px solid #ddd;
            height: 36px;
            font-size: 18px;
            font-weight: 600;
            line-height: 36px;
            margin: 0;
            padding: 0 36px 0 16px
        }

        #wp-link-close {
            color: #666;
            padding: 0;
            position: absolute;
            top: 0;
            right: 0;
            width: 36px;
            height: 36px;
            text-align: center;
            background: 0 0;
            border: none;
            cursor: pointer
        }

        #wp-link-close:before {
            font: normal 20px/36px dashicons;
            vertical-align: top;
            speak: none;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            width: 36px;
            height: 36px;
            content: "\f158"
        }

        #wp-link-close:focus, #wp-link-close:hover {
            color: #00a0d2
        }

        #wp-link-close:focus {
            outline: 0;
            box-shadow: 0 0 0 1px #5b9dd9, 0 0 2px 1px rgba(30, 140, 190, .8)
        }

        #wp-link-wrap #link-selector {
            -webkit-overflow-scrolling: touch;
            padding: 0 16px;
            position: absolute;
            top: 37px;
            left: 0;
            right: 0;
            bottom: 44px
        }

        #wp-link ol, #wp-link ul {
            list-style: none;
            margin: 0;
            padding: 0
        }

        #wp-link input[type=text] {
            box-sizing: border-box
        }

        #wp-link #link-options {
            padding: 8px 0 12px
        }

        #wp-link p.howto {
            margin: 3px 0
        }

        #wp-link p.howto a {
            text-decoration: none;
            color: inherit
        }

        #wp-link label input[type=text] {
            margin-top: 5px;
            width: 70%
        }

        #wp-link #link-options label span, #wp-link #search-panel label span.search-label {
            display: inline-block;
            width: 80px;
            text-align: right;
            padding-right: 5px;
            max-width: 24%;
            vertical-align: middle;
            word-wrap: break-word
        }

        #wp-link .link-search-field {
            float: left;
            width: 250px;
            max-width: 70%
        }

        #wp-link .link-search-wrapper {
            margin: 5px 0 9px;
            display: block;
            overflow: hidden
        }

        #wp-link .link-search-wrapper span {
            float: left;
            margin-top: 4px
        }

        #wp-link .link-search-wrapper .spinner {
            margin-top: 5px
        }

        #wp-link .link-target {
            padding: 3px 0 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis
        }

        #wp-link .link-target label {
            max-width: 70%
        }

        #wp-link .query-results {
            border: 1px #dfdfdf solid;
            margin: 0 0 12px;
            background: #fff;
            overflow: auto;
            position: absolute;
            left: 16px;
            right: 16px;
            bottom: 0;
            top: 166px
        }

        .has-text-field #wp-link .query-results {
            top: 200px
        }

        #wp-link li {
            clear: both;
            margin-bottom: 0;
            border-bottom: 1px solid #f1f1f1;
            color: #32373c;
            padding: 4px 6px 4px 10px;
            cursor: pointer;
            position: relative
        }

        #wp-link .query-notice {
            padding: 0;
            border-bottom: 1px solid #dfdfdf;
            background-color: #f7fcfe;
            color: #000
        }

        #wp-link .query-notice .query-notice-default, #wp-link .query-notice .query-notice-hint {
            display: block;
            padding: 6px;
            border-left: 4px solid #00a0d2
        }

        #wp-link .unselectable.no-matches-found {
            padding: 0;
            border-bottom: 1px solid #dfdfdf;
            background-color: #fef7f1
        }

        #wp-link .no-matches-found .item-title {
            display: block;
            padding: 6px;
            border-left: 4px solid #d54e21
        }

        #wp-link .query-results em {
            font-style: normal
        }

        #wp-link li:hover {
            background: #eaf2fa;
            color: #151515
        }

        #wp-link li.unselectable {
            border-bottom: 1px solid #dfdfdf
        }

        #wp-link li.unselectable:hover {
            background: #fff;
            cursor: auto;
            color: #32373c
        }

        #wp-link li.selected {
            background: #ddd;
            color: #32373c
        }

        #wp-link li.selected .item-title {
            font-weight: 600
        }

        #wp-link li:last-child {
            border: none
        }

        #wp-link .item-title {
            display: inline-block;
            width: 80%;
            width: calc(100% - 68px);
            word-wrap: break-word
        }

        #wp-link .item-info {
            text-transform: uppercase;
            color: #666;
            font-size: 11px;
            position: absolute;
            right: 5px;
            top: 5px
        }

        #wp-link .river-waiting {
            display: none;
            padding: 10px 0
        }

        #wp-link .submitbox {
            padding: 8px 16px;
            background: #fcfcfc;
            border-top: 1px solid #ddd;
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0
        }

        #wp-link-cancel {
            line-height: 25px;
            float: left
        }

        #wp-link-update {
            line-height: 23px;
            float: right
        }

        #wp-link-submit {
            float: right
        }

        @media screen and (max-width: 782px) {
            #wp-link-wrap {
                margin-top: -140px
            }

            #wp-link-wrap .query-results {
                top: 195px
            }

            #wp-link-wrap.has-text-field .query-results {
                top: 235px
            }

            #link-selector {
                padding: 0 16px 60px
            }

            #wp-link-wrap #link-selector {
                bottom: 52px
            }

            #wp-link-cancel {
                line-height: 32px
            }

            #wp-link .link-target {
                padding-top: 10px
            }

            #wp-link .submitbox .button {
                margin-bottom: 0
            }
        }

        @media screen and (max-width: 520px) {
            #wp-link-wrap {
                width: auto;
                margin-left: 0;
                left: 10px;
                right: 10px;
                max-width: 500px
            }
        }

        @media screen and (max-height: 520px) {
            #wp-link-wrap {
                transition: none;
                height: auto;
                margin-top: 0;
                top: 10px;
                bottom: 10px
            }

            #link-selector {
                overflow: auto
            }

            #search-panel .query-results {
                position: static
            }
        }

        @media screen and (max-height: 290px) {
            #wp-link-wrap {
                height: auto;
                margin-top: 0;
                top: 10px;
                bottom: 10px
            }

            #link-selector {
                overflow: auto;
                height: calc(100% - 92px);
                padding-bottom: 2px
            }

            #search-panel .query-results {
                position: static
            }
        }

        div.wp-link-preview {
            float: left;
            margin: 5px;
            max-width: 694px;
            overflow: hidden;
            text-overflow: ellipsis
        }

        div.wp-link-preview a {
            color: #0073aa;
            text-decoration: underline;
            transition-property: border, background, color;
            transition-duration: .05s;
            transition-timing-function: ease-in-out;
            cursor: pointer
        }

        div.wp-link-preview a.wplink-url-error {
            color: #dc3232
        }

        div.wp-link-input {
            float: left;
            margin: 2px;
            max-width: 694px
        }

        div.wp-link-input input {
            width: 300px;
            padding: 3px;
            box-sizing: border-box
        }

        .mce-toolbar div.wp-link-input ~ .mce-btn, .mce-toolbar div.wp-link-preview ~ .mce-btn {
            margin: 2px 1px
        }

        .mce-inline-toolbar-grp .mce-btn-group .mce-btn:last-child {
            margin-right: 2px
        }

        .ui-autocomplete.wplink-autocomplete {
            z-index: 100110;
            max-height: 200px;
            overflow-y: auto;
            padding: 0;
            margin: 0;
            list-style: none;
            position: absolute;
            border: 1px solid #5b9dd9;
            box-shadow: 0 1px 2px rgba(30, 140, 190, .8);
            background-color: #fff
        }

        .ui-autocomplete.wplink-autocomplete li {
            margin-bottom: 0;
            padding: 4px 10px;
            clear: both;
            white-space: normal;
            text-align: left
        }

        .ui-autocomplete.wplink-autocomplete li .wp-editor-float-right {
            float: right
        }

        .ui-autocomplete.wplink-autocomplete li.ui-state-focus {
            background-color: #ddd;
            cursor: pointer
        }

        @media screen and (max-width: 782px) {
            div.wp-link-input, div.wp-link-preview {
                max-width: 70%;
                max-width: calc(100% - 86px)
            }

            div.wp-link-preview {
                margin: 8px 0 8px 5px
            }

            div.wp-link-input {
                width: 300px
            }

            div.wp-link-input input {
                width: 100%;
                font-size: 16px;
                padding: 5px
            }
        }

        .mce-fullscreen {
            z-index: 100010
        }

        .rtl .quicktags-toolbar input, .rtl .wp-switch-editor {
            font-family: Tahoma, sans-serif
        }

        .mce-rtl .mce-flow-layout .mce-flow-layout-item > div {
            direction: rtl
        }

        .mce-rtl .mce-listbox i.mce-caret {
            left: 6px
        }

        html:lang(he-il) .rtl .quicktags-toolbar input, html:lang(he-il) .rtl .wp-switch-editor {
            font-family: Arial, sans-serif
        }

        @media print,(-webkit-min-device-pixel-ratio: 1.25),(min-resolution: 120dpi) {
            .wp-media-buttons .add_media span.wp-media-buttons-icon {
                background: 0 0
            }
        }
    </style>

</div>
<!-- /.wdt-datatables-admin-wrap .wrap -->
