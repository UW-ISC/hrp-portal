<?php

/**
 * PHP-DI repository bindings.
 *
 * Each `wpdatatables_*` repository is bound by its interface here, constructed
 * with its table name resolved from the matching install-action descriptor
 * (mirrors the ivyforms `repository.php` pattern). Table names are irregular
 * (`wpdatacharts`, `wpdatatables` with no suffix), so the descriptor's
 * `getTableName()` is the single source of truth.
 *
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

use WPDataTables\Repository\Cache\CacheRepository;
use WPDataTables\Repository\Cache\CacheRepositoryInterface;
use WPDataTables\Repository\Chart\ChartRepository;
use WPDataTables\Repository\Chart\ChartRepositoryInterface;
use WPDataTables\Repository\Column\ColumnRepository;
use WPDataTables\Repository\Column\ColumnRepositoryInterface;
use WPDataTables\Repository\Permissions\AccessRulesRepository;
use WPDataTables\Repository\Rows\RowsRepository;
use WPDataTables\Repository\Rows\RowsRepositoryInterface;
use WPDataTables\Repository\Table\TableRepository;
use WPDataTables\Repository\Table\TableRepositoryInterface;
use WPDataTables\Repository\Template\TemplateRepository;
use WPDataTables\Repository\Template\TemplateRepositoryInterface;
use WPDataTables\Services\InstallActions\DB\CacheTable;
use WPDataTables\Services\InstallActions\DB\ChartsTable;
use WPDataTables\Services\InstallActions\DB\ColumnsTable;
use WPDataTables\Services\InstallActions\DB\RowsTable;
use WPDataTables\Services\InstallActions\DB\TablesTable;
use WPDataTables\Services\InstallActions\DB\TemplatesTable;

return [

    TableRepositoryInterface::class => function () {
        return new TableRepository(TablesTable::getTableName());
    },

    ColumnRepositoryInterface::class => function () {
        return new ColumnRepository(ColumnsTable::getTableName());
    },

    ChartRepositoryInterface::class => function () {
        return new ChartRepository(ChartsTable::getTableName());
    },

    RowsRepositoryInterface::class => function () {
        return new RowsRepository(RowsTable::getTableName());
    },

    CacheRepositoryInterface::class => function () {
        return new CacheRepository(CacheTable::getTableName());
    },

    TemplateRepositoryInterface::class => function () {
        return new TemplateRepository(TemplatesTable::getTableName());
    },

    AccessRulesRepository::class => function () {
        return new AccessRulesRepository();
    },
];
