<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Factory\Column;

use WPDataTables\Entity\Column\AttachmentColumn;
use WPDataTables\Entity\Column\CartColumn;
use WPDataTables\Entity\Column\DateColumn;
use WPDataTables\Entity\Column\DateTimeColumn;
use WPDataTables\Entity\Column\EmailColumn;
use WPDataTables\Entity\Column\FileColumn;
use WPDataTables\Entity\Column\FloatColumn;
use WPDataTables\Entity\Column\FormulaColumn;
use WPDataTables\Entity\Column\HiddenColumn;
use WPDataTables\Entity\Column\ImageColumn;
use WPDataTables\Entity\Column\IndexColumn;
use WPDataTables\Entity\Column\IntColumn;
use WPDataTables\Entity\Column\LinkColumn;
use WPDataTables\Entity\Column\MasterdetailColumn;
use WPDataTables\Entity\Column\RuntimeColumn;
use WPDataTables\Entity\Column\SelectColumn;
use WPDataTables\Entity\Column\StringColumn;
use WPDataTables\Entity\Column\TimeColumn;
use WDTColumn;

/**
 * Creates typed runtime column formatters by column type slug.
 *
 * All whitelisted column types are instantiated from
 * {@see WPDataTables\Entity\Column\*} classes. {@see createLegacyColumn()} remains
 * as a fallback when an add-on filters in a legacy formatter path.
 *
 * @package WPDataTables\Factory\Column
 */
class ColumnTypeFactory
{
    /**
     * Types whose logic lives under backend/src/Entity/Column/.
     *
     * @var array<string, class-string<\WDTColumn>>
     */
    private const MIGRATED_TYPES = [
        'string'   => StringColumn::class,
        'int'      => IntColumn::class,
        'float'    => FloatColumn::class,
        'date'     => DateColumn::class,
        'datetime' => DateTimeColumn::class,
        'time'     => TimeColumn::class,
        'email'    => EmailColumn::class,
        'image'    => ImageColumn::class,
        'link'     => LinkColumn::class,
        'select'   => SelectColumn::class,
        'cart'     => CartColumn::class,
        'formula'      => FormulaColumn::class,
        'hidden'       => HiddenColumn::class,
        'index'        => IndexColumn::class,
        'file'         => FileColumn::class,
        'attachment'   => AttachmentColumn::class,
        'masterdetail' => MasterdetailColumn::class,
    ];

    /**
     * @var array<int, string>
     */
    private const VALID_TYPES = [
        'string',
        'int',
        'float',
        'date',
        'datetime',
        'time',
        'link',
        'email',
        'image',
        'file',
        'formula',
        'masterdetail',
        'attachment',
        'select',
        'cart',
        'hidden',
        'index',
    ];

    /**
     * @param string               $wdtColumnType
     * @param array<string, mixed> $properties
     *
     * @return WDTColumn
     */
    public function create(string $wdtColumnType = 'string', array $properties = []): WDTColumn
    {
        $wdtColumnType = $this->normalizeType($wdtColumnType);

        if (isset(self::MIGRATED_TYPES[$wdtColumnType])) {
            $class = apply_filters(
                'wpdatatables_column_type_class',
                self::MIGRATED_TYPES[$wdtColumnType],
                $wdtColumnType,
                $properties
            );

            return new $class($properties);
        }

        return $this->createLegacyColumn($wdtColumnType, $properties);
    }

    /**
     * @param string $wdtColumnType
     *
     * @return string
     */
    private function normalizeType(string $wdtColumnType): string
    {
        if ($wdtColumnType === '') {
            $wdtColumnType = 'string';
        }

        $wdtColumnType = strtolower(sanitize_text_field($wdtColumnType));

        if (!in_array($wdtColumnType, self::VALID_TYPES, true)) {
            return 'string';
        }

        return $wdtColumnType;
    }

    /**
     * Legacy require_once + global class instantiation for unmigrated types.
     *
     * @param string               $wdtColumnType
     * @param array<string, mixed> $properties
     *
     * @return WDTColumn
     */
    private function createLegacyColumn(string $wdtColumnType, array $properties): WDTColumn
    {
        $columnObj = ucfirst($wdtColumnType) . 'WDTColumn';
        $columnFormatterFileName = 'class.' . $wdtColumnType . '.wpdatacolumn.php';
        $columnFormatterFileName = apply_filters(
            'wpdatatables_column_formatter_file_name',
            $columnFormatterFileName,
            $wdtColumnType
        );

        $columnFile = $this->resolveLegacyColumnFile($columnFormatterFileName);
        require_once $columnFile;

        return new $columnObj($properties);
    }

    /**
     * Resolve a legacy column formatter path.
     *
     * Bare filenames (e.g. class.int.wpdatacolumn.php) were historically loaded
     * relative to backend/src/Legacy/Facades/ because generateColumn lived in class.wpdatacolumn.php.
     * Tier integrations may filter in a full path instead.
     *
     * @param string $columnFormatterFileName
     *
     * @return string
     */
    private function resolveLegacyColumnFile(string $columnFormatterFileName): string
    {
        if ($columnFormatterFileName === '') {
            return WDT_LEGACY_FACADES_PATH . 'class.string.wpdatacolumn.php';
        }

        if (is_file($columnFormatterFileName)) {
            return $columnFormatterFileName;
        }

        if (strpos($columnFormatterFileName, '/') !== false || strpos($columnFormatterFileName, '\\') !== false) {
            return $columnFormatterFileName;
        }

        return WDT_LEGACY_FACADES_PATH . basename($columnFormatterFileName);
    }
}
