<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Rest;

use WPDataTables\Common\Exceptions\ForbiddenException;
use WPDataTables\Common\Exceptions\InvalidArgumentException;
use WPDataTables\Common\Exceptions\NotFoundException;
use WPDataTables\PublicApi\Services\PublicApiTableAccess;
use WPDataTables\Services\Table\TableConfigService;

/**
 * Shared read logic for column REST endpoints (admin + public).
 *
 * @package WPDataTables\Services\Rest
 */
class ColumnReadService
{
    /** @var TableConfigService */
    private $tableConfigService;

    public function __construct(TableConfigService $tableConfigService)
    {
        $this->tableConfigService = $tableConfigService;
    }

    /**
     * @param int $tableId
     * @return array<int, mixed>
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    public function listColumns(int $tableId)
    {
        if ($tableId === 0) {
            throw new InvalidArgumentException('A valid table id is required.');
        }

        if (!$this->tableConfigService->loadTableFromDB($tableId, false)) {
            throw new NotFoundException('Table not found.');
        }

        $columns = $this->tableConfigService->loadColumnsFromDB($tableId);

        return $columns ? $columns : [];
    }

    /**
     * @param int $tableId
     * @return array<int, mixed>
     * @throws ForbiddenException
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    public function listColumnsForPublicApi(int $tableId)
    {
        PublicApiTableAccess::assertCanAccess($tableId);

        return $this->listColumns($tableId);
    }

    /**
     * @param int $tableId
     * @param int $columnId
     * @return array<string, mixed>
     * @throws ForbiddenException
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    public function getColumnForPublicApi(int $tableId, int $columnId)
    {
        PublicApiTableAccess::assertCanAccess($tableId);

        return $this->getColumn($tableId, $columnId);
    }

    /**
     * @param int $tableId
     * @param int $columnId
     * @return array<string, mixed>
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    public function getColumn(int $tableId, int $columnId)
    {
        if ($tableId === 0 || $columnId === 0) {
            throw new InvalidArgumentException('A valid table id and column id are required.');
        }

        $column = $this->tableConfigService->loadSingleColumnFromDB($columnId);

        if (!$column || (int) $column['table_id'] !== $tableId) {
            throw new NotFoundException('Column not found.');
        }

        return ['column' => $column];
    }
}
