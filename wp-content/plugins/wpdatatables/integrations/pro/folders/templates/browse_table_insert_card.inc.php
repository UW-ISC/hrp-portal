<?php defined('ABSPATH') or die('Access denied.');

global $wpdb;
$allItems = 0;
$unassignedItems = 0;
$itemsType = 'table';
$itemInstance = '';
if (isset($_GET['page'])) {
    $itemInstance = WPDataFoldersFactory::createPageBased($_GET['page']);
}
if ($itemInstance instanceof WPDataTablesFolders ||
    $itemInstance instanceof WPDataChartsFolders ||
    $itemInstance instanceof WPDataReportsFolders
) {
    $allItems = count($itemInstance->getAll());
    $unassignedItems = intval($itemInstance->getAllUnassigned());
    $itemsType = $itemInstance->getType();
    $itemsTypeCaps = ucfirst($itemInstance->getType()) . 's';
}

?>

<div class="card wpdt-folders-card" data-type="<?php esc_attr_e($itemsType) ?>">
    <!-- Preloader -->
    <?php include WDT_TEMPLATE_PATH . 'admin/common/preloader.inc.php'; ?>
    <!-- /Preloader -->
    <div class="close-folders-card"
         data-toggle="tooltip"
         title="<?php esc_attr_e('Collapse folders', 'wpdatatables') ?>"
    >
        <i class="wpdt-icon-angle-left"></i>
        <i class="wpdt-icon-angle-right hidden"></i>
    </div>

    <?php wp_nonce_field('wdtFoldersNonce', 'wdtFoldersNonce'); ?>

    <div class="card-wrapper">
        <!-- .card-header -->
        <div class="card-header wdt-admin-folders-card-header ch-alt">
            <?php if ($itemsType == 'report') { ?>
                <img id="wpdt-folders-inline-logo"
                     src="<?php echo WDT_RB_ROOT_URL; ?>assets/img/Report-builder.svg"/>
            <?php } else { ?>
                <img id="wpdt-folders-inline-logo"
                     src="<?php echo WDT_ROOT_URL; ?>assets/img/logo.svg"/>
            <?php } ?>
            <h2>
                <?php esc_html_e('Folders', 'wpdatatables'); ?>
            </h2>
        </div>
        <!--/ .card-header -->

        <!-- .card-body -->
        <div class="card-body">
            <div class="buttons">
                <button
                        id="wpdt-create-folder-btn"
                        class="btn btn-primary"
                        data-toggle="tooltip"
                        title=" <?php esc_attr_e('Create Folder', 'wpdatatables') ?>">

                </button>
                <button
                        id="wpdt-edit-folder-btn"
                        class="btn btn-warning disabled"
                        data-toggle="tooltip"
                        title=" <?php esc_attr_e('Edit Folder', 'wpdatatables') ?>">

                </button>
                <button
                        id="wpdt-delete-folder-btn"
                        class="btn btn-danger disabled"
                        data-toggle="tooltip"
                        title="<?php esc_attr_e('Delete Folder', 'wpdatatables') ?>">
                </button>
                <button
                        id="wpdt-all-open-close-folder-btn"
                        class="btn btn-warning"
                        data-toggle="tooltip"
                        title="<?php esc_attr_e('Open/close all folders', 'wpdatatables') ?>">
                </button>
                <button
                        id="wpdt-sort-folder-btn"
                        class="btn btn-warning"
                        data-toggle="tooltip"
                        title="<?php esc_attr_e('Choose sort option', 'wpdatatables') ?>">
                </button>
                <ul class="sort-menu hidden">
                    <li class=""><a href="#" data-value="ASC"><?php esc_html_e('A -> Z', 'wpdatatables') ?></a></li>
                    <li class=""><a href="#" data-value="DESC"><?php esc_html_e('Z -> A', 'wpdatatables') ?></a></li>
                    <li class=""><a href="#" data-value="NEW"><?php esc_html_e('Sort by newest', 'wpdatatables') ?></a>
                    </li>
                    <li class=""><a href="#" data-value="OLD"><?php esc_html_e('Sort by oldest', 'wpdatatables') ?></a>
                    </li>
                </ul>
                <button
                        id="wpdt-show-columns-btn"
                        class="btn btn-warning"
                        data-toggle="tooltip"
                        title="<?php esc_attr_e('Show/hide browse columns', 'wpdatatables') ?>">
                </button>
                <ul class="columns-menu hidden">
                    <?php
                    if ($itemInstance->getType() == 'table') {
                        ?>
                        <li class="active">
                            <a href="#" data-value="id"><?php esc_html_e('ID', 'wpdatatables'); ?></a>
                        </li>
                        <li class="active">
                            <a href="#" data-value="title"><?php esc_html_e('Title', 'wpdatatables'); ?></a></li>
                        <li class="active">
                            <a href="#" data-value="folders"><?php esc_html_e('Folders', 'wpdatatables'); ?></a>
                        </li>
                        <?php
                        if (!$itemInstance->useNotSupportedMySQLVersion()) {
                            ?>
                            <li class="active"><a href="#"
                                                  data-value="table_description"><?php esc_html_e('Description', 'wpdatatables'); ?></a>
                            </li>
                            <?php
                        }
                        ?>
                        <li class="active">
                            <a href="#" data-value="table_type"><?php esc_html_e('Type', 'wpdatatables'); ?></a>
                        </li>
                        <?php  if (get_option('wdtUseSeparateCon')) { ?>
                            <li class="active">
                                <a href="#" data-value="connection"><?php esc_html_e('Connection', 'wpdatatables'); ?></a>
                            </li>
                        <?php }
                    } else if ($itemInstance->getType() == 'chart') {
                        ?>
                        <li class="active">
                            <a href="#" data-value="id"><?php esc_html_e('ID', 'wpdatatables'); ?></a>
                        </li>
                        <li class="active">
                            <a href="#" data-value="title"><?php esc_html_e('Title', 'wpdatatables'); ?></a></li>
                        <li class="active">
                            <a href="#" data-value="folders"><?php esc_html_e('Folders', 'wpdatatables'); ?></a>
                        </li>
                        <li class="active">
                            <a href="#" data-value="engine"><?php esc_html_e('Render Engine', 'wpdatatables'); ?></a>
                        </li>
                        <li class="active">
                            <a href="#" data-value="type"><?php esc_html_e('Chart Type', 'wpdatatables'); ?></a>
                        </li>
                        <?php
                    } else if ($itemInstance->getType() == 'report') {
                        ?>
                        <li class="active">
                            <a href="#" data-value="id"><?php esc_html_e('ID', 'wpdatatables'); ?></a>
                        </li>
                        <li class="active">
                            <a href="#" data-value="name"><?php esc_html_e('Name', 'wpdatatables'); ?></a></li>
                        <li class="active">
                            <a href="#" data-value="folders"><?php esc_html_e('Folders', 'wpdatatables'); ?></a>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
                <!--            <button-->
                <!--                    id="wpdt-checkbox-folder-btn"-->
                <!--                    class="btn btn-warning"-->
                <!--                    data-toggle="tooltip"-->
                <!--                    title="-->
                <?php //esc_attr_e('Check multiple folders', 'wpdatatables')  ?><!--">-->
                <!--            </button>-->
            </div>
            <div class="wpdt-folders-search">
                <div class="wpdt-search-box search-box">
                    <div class="fg-line">
                        <i class="wpdt-icon-search"></i>
                        <label class="screen-reader-text"
                               for="wpdt-folder-search"><?php esc_html_e('search:', 'wpdatatables') ?></label>
                        <input type="search" placeholder="<?php esc_attr_e('Search folders...', 'wpdatatables') ?>"
                               id="wpdt-folder-search" name="" value="">
                    </div>
                </div>
            </div>
            <div class="wpdt-folders-sparator"></div>
            <div class="wpdt-folders-sum jstree jstree-default jstree-leaf">
                <ul class="wpdt-tree jstree-container-ul" role="none">
                    <li class="wpdt-root-folder jstree-node" id="-1">
                        <a class="jstree-anchor wpdt-anchor"
                           id="wpdt-all-folder_anchor"
                        >
                            <i class="wpdt-icon-folder-close1" role="none"></i>
                            <?php esc_html_e('All ' . $itemsTypeCaps, 'wpdatatables'); ?>
                            <span class="wpdt-folder-actions">
                             <span class="wpdt-count"><?php echo $allItems ?></span>
                            </span>
                        </a>
                    </li>
                    <li class="wpdt-unassigned-folder jstree-node" id="0">
                        <a class="jstree-anchor wpdt-anchor"
                           id="wpdt-unassigned-folder_anchor">
                            <i class="wpdt-icon-folder-close1" role="none"></i>
                            <?php esc_html_e('Unassigned ' . $itemsTypeCaps, 'wpdatatables'); ?>
                            <span class="wpdt-folder-actions">
                                <span class="wpdt-count"><?php echo $unassignedItems ?></span>
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
            <div id="wpdt-folder-tree-wrapper">
                <div id="wpdt-folder-tree" class="wpdt-folder-tree hidden"></div>
            </div>
        </div>
        <!--/ .card-body -->
    </div>
</div>


<div class="modal fade" style="display: none" aria-hidden="true" data-action="" id="wpdt-folder-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                <input type="text" id="wpdt-folder-name" class="form-control" placeholder="Enter folder name">
                <p id="wpdt-folder-unique-error" class="wpdt-error hidden">
                    <?php esc_html_e('Folder name is already in use. Please choose a unique name.', 'wpdatatables'); ?>
                </p>
                <p id="wpdt-folder-empty-error" class="wpdt-error hidden">
                    <?php esc_html_e('Folder name can not be empty. Please insert folder name.', 'wpdatatables'); ?>
                </p>
                <?php if ($itemsType == 'table') { ?>
                <p class="wpdt-folder-delete-notice" class="hidden">
                    <?php esc_html_e('Tables in the folder will not be deleted.', 'wpdatatables'); ?>
                </p>
                <p class="wpdt-folder-remove-notice" class="hidden">
                    <?php esc_html_e('Table will not be deleted.', 'wpdatatables'); ?>
                </p>
                <?php } else if ($itemsType == 'chart') { ?>
                    <p class="wpdt-folder-delete-notice" class="hidden">
                        <?php esc_html_e('Charts in the folder will not be deleted.', 'wpdatatables'); ?>
                    </p>
                    <p class="wpdt-folder-remove-notice" class="hidden">
                        <?php esc_html_e('Chart will not be deleted.', 'wpdatatables'); ?>
                    </p>

                <?php } else if ($itemsType == 'report') { ?>
                    <p class="wpdt-folder-delete-notice" class="hidden">
                        <?php esc_html_e('Reports in the folder will not be deleted.', 'wpdatatables'); ?>
                    </p>
                    <p class="wpdt-folder-remove-notice" class="hidden">
                        <?php esc_html_e('Report will not be deleted.', 'wpdatatables'); ?>
                    </p>
                <?php } ?>

            </div>
            <div class="modal-footer">
                <hr>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"
                        id="wpdt-cancel-folder-modal"><?php esc_html_e('Cancel', 'wpdatatables'); ?></button>
                <button type="button" class="btn btn-danger" data-dismiss="modal" id="wpdt-delete-folder-modal">
                    <i class="wpdt-icon-trash"></i>
                    <?php esc_html_e('Delete', 'wpdatatables'); ?>
                </button>
                <button type="button" class="btn btn-danger" data-dismiss="modal" id="wpdt-remove-folder-modal">
                    <i class="wpdt-icon-trash"></i>
                    <?php esc_html_e('Remove', 'wpdatatables'); ?>
                </button>
                <button type="button" class="btn btn-primary" data-dismiss="modal" id="wpdt-save-folder-modal">
                    <i class="wpdt-icon-save"></i>
                    <?php esc_html_e('Save', 'wpdatatables'); ?>
                </button>
            </div>
        </div>
    </div>
</div>