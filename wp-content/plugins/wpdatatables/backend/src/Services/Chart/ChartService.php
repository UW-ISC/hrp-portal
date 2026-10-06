<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\Chart;

use WPDataTables\Repository\Chart\ChartRepositoryInterface;

/**
 * Chart domain service — auth-decoupled delete.
 *
 * Build/save/render orchestration lives in {@see ChartEngineService}; the global
 * {@see \WPDataChart} facade delegates delete here after its admin nonce/cap gate.
 *
 * @package WPDataTables\Services\Chart
 */
class ChartService
{
    /** @var ChartRepositoryInterface */
    private $chartRepository;

    public function __construct(ChartRepositoryInterface $chartRepository)
    {
        $this->chartRepository = $chartRepository;
    }

    /**
     * Delete a chart by id.
     *
     * Auth-decoupled counterpart to {@see \WPDataChart::delete()}: there is no
     * internal nonce/capability check — callers gate auth upstream (the REST
     * `permission_callback`, or the legacy static wrapper which keeps its nonce
     * check). Drops the row, then fires the `wpdatatables_after_delete_charts`
     * action.
     *
     * @param int $id The chart id.
     *
     * @return bool Whether the chart was deleted.
     */
    public function deleteChart(int $id): bool
    {
        if (empty($id)) {
            return false;
        }

        $this->chartRepository->deleteById($id);

        do_action('wpdatatables_after_delete_charts', $id, 'chart');

        return true;
    }
}
