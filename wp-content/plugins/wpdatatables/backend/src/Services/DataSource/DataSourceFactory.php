<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Services\DataSource;

use WPDataTables\Common\Exceptions\InvalidArgumentException;
use WPDataTables\Vendor\Psr\Container\ContainerInterface;

/**
 * Resolves the {@see DataSourceInterface} adapter for a given source type.
 *
 * The type keys are the source identifiers used across the plugin (e.g. `json`,
 * `nested_json`, `serialized`, `google_spreadsheet`, `xml`, `excel`). Adapters
 * are pulled lazily from the DI container, so each is built (and its own
 * dependencies autowired) only when its source type is actually used.
 *
 * @package WPDataTables\Services\DataSource
 */
class DataSourceFactory
{
    public const JSON = 'json';
    public const NESTED_JSON = 'nested_json';
    public const SERIALIZED = 'serialized';
    public const GOOGLE_SPREADSHEET = 'google_spreadsheet';
    public const XML = 'xml';
    public const EXCEL = 'excel';
    public const MANUAL = 'manual';
    public const MYSQL = 'mysql';

    /** @var array<string, class-string<DataSourceInterface>> */
    private const MAP = [
        self::JSON => JsonDataSource::class,
        self::NESTED_JSON => NestedJsonDataSource::class,
        self::SERIALIZED => SerializedPhpDataSource::class,
        self::GOOGLE_SPREADSHEET => GoogleSheetsDataSource::class,
        self::XML => XmlDataSource::class,
        self::EXCEL => ExcelDataSource::class,
        self::MANUAL => ManualDataSource::class,
        self::MYSQL => MySqlQueryDataSource::class,
    ];

    /** @var ContainerInterface */
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $type One of the source-type constants above.
     *
     * @return DataSourceInterface
     * @throws InvalidArgumentException When the source type has no adapter.
     */
    public function make(string $type): DataSourceInterface
    {
        if (!isset(self::MAP[$type])) {
            throw new InvalidArgumentException(
                sprintf('No data source adapter registered for type "%s".', $type)
            );
        }

        return $this->container->get(self::MAP[$type]);
    }

    /**
     * Resolve an adapter from a wpDataTables `table_type` value.
     *
     * Manual and MySQL tables share the query-based adapter; Excel covers both
     * `xls` and `csv` table types.
     *
     * @param string $tableType Value stored in `wpdatatables.table_type`.
     *
     * @return DataSourceInterface
     * @throws InvalidArgumentException When the table type has no adapter.
     */
    public function makeForTableType(string $tableType): DataSourceInterface
    {
        $map = self::tableTypeMap();
        if (!isset($map[$tableType])) {
            throw new InvalidArgumentException(
                sprintf('No data source adapter registered for table type "%s".', $tableType)
            );
        }

        return $this->make($map[$tableType]);
    }

    /**
     * All registered source-type keys.
     *
     * @return list<string>
     */
    public static function supportedTypes(): array
    {
        return array_keys(self::MAP);
    }

    /**
     * Map wpDataTables `table_type` values to {@see self} source keys.
     *
     * @return array<string, string>
     */
    public static function tableTypeMap(): array
    {
        return [
            'json'                => self::JSON,
            'nested_json'         => self::NESTED_JSON,
            'serialized'          => self::SERIALIZED,
            'google_spreadsheet'  => self::GOOGLE_SPREADSHEET,
            'xml'                 => self::XML,
            'xls'                 => self::EXCEL,
            'csv'                 => self::EXCEL,
            'mysql'               => self::MYSQL,
            'manual'              => self::MYSQL,
        ];
    }
}
