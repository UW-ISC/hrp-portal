<?php defined('ABSPATH') or die('Access denied.');
?>

<div class="card column-settings ">

    <!-- Preloader -->
    <?php include WDT_TEMPLATE_PATH . 'admin/common/preloader.inc.php'; ?>
    <!-- /Preloader -->

    <div class="card-header wdt-admin-card-header ch-alt">
        <div class="col-sm-8 p-l-0 p-t-5">
            <h2><?php _e('Table preview', 'wpdatatables'); ?></h2>
        </div>

        <div class="clear"></div>
    </div>
    <!-- /.card-header -->
    <div class="card-body card-padding">
        <div id="wpdt-views">
            <ul class="nav nav-pills">
                <li class="nav-item active" data-view="desktop">
                    <a class="nav-link" ><?php _e('Desktop', 'wpdatatables'); ?></a>
                </li>
                <li class="nav-item" data-view="tablet">
                    <a class="nav-link"><?php _e('Tablet', 'wpdatatables'); ?></a>
                </li>
                <li class="nav-item" data-view="mobile">
                    <a class="nav-link"><?php _e('Mobile', 'wpdatatables'); ?></a>
                </li>
            </ul>
        </div>
        <div id="wpdt-view-container">
            <?php if(isset($tableData)) {?>
            <div class="wpdt-c wpDataTablesSimpleWrapper-<?php echo $tableData->tableID;?>">
                <?php echo $tableData->wdtHtml; ?>
            </div>
            <?php } ?>
        </div>
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card /.column-settings -->
