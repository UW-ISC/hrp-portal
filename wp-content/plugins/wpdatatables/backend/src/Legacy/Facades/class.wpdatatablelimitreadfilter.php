<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use WPDataTables\Infrastructure\Excel\LimitReadFilter;

/**
 * Backward-compatible subclass. The implementation lives in
 * {@see WPDataTables\Infrastructure\Excel\LimitReadFilter}. This subclass is kept
 * so the legacy global class name and `instanceof IReadFilter` checks keep working.
 */
class wpDataTableLimitReadFilter extends LimitReadFilter
{
}
