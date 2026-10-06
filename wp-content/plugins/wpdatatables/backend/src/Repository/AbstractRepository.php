<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace WPDataTables\Repository;

use WPDataTables\Common\Exceptions\QueryExecutionException;

/**
 * Base repository over a single `wpdatatables_*` table.
 *
 * Holds the WordPress `$wpdb`, the table name, and a `FACTORY` const used to
 * hydrate database rows into entities — copied from the ivyforms
 * AbstractRepository pattern. Concrete repositories override `FACTORY` and the
 * column-allowlist hooks used by `search()`. External (non-$wpdb) data access
 * belongs in Infrastructure\Db, not here.
 *
 * @package WPDataTables\Repository
 */
abstract class AbstractRepository implements BaseRepositoryInterface
{
    /** Concrete repos override with `SomeFactory::class`. */
    public const FACTORY = '';

    /** @var string */
    protected string $table;

    /** @var \wpdb */
    protected $wpdb;

    public function __construct(string $table)
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table = $table;
    }

    public function selectQuery(): string
    {
        return 'SELECT * FROM ' . $this->table;
    }

    /**
     * @param int $id
     * @return object|null
     */
    public function getById(int $id): ?object
    {
        $row = $this->wpdb->get_row(
            $this->wpdb->prepare($this->selectQuery() . " WHERE {$this->table}.id = %d", $id),
            ARRAY_A
        );

        if (!$row) {
            return null;
        }

        return call_user_func([static::FACTORY, 'create'], $row);
    }

    /**
     * @return mixed[]
     */
    public function getAll(): array
    {
        $rows = $this->wpdb->get_results($this->selectQuery(), ARRAY_A);

        $result = [];
        foreach ((array) $rows as $row) {
            $result[] = call_user_func([static::FACTORY, 'create'], $row);
        }

        return $result;
    }

    /**
     * @param int $id
     * @return int
     * @throws QueryExecutionException
     */
    public function delete(int $id): int
    {
        $result = $this->wpdb->query(
            $this->wpdb->prepare("DELETE FROM {$this->table} WHERE id = %d", $id)
        );

        if ($result === false) {
            throw new QueryExecutionException("failed_delete_by_id:{$id}:{$this->table}");
        }

        return (int) $result;
    }

    /**
     * @param array<int> $ids
     * @return int
     * @throws QueryExecutionException
     */
    public function deleteMany(array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }

        $ids = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $result = $this->wpdb->query(
            $this->wpdb->prepare("DELETE FROM {$this->table} WHERE id IN ($placeholders)", ...$ids)
        );

        if ($result === false) {
            throw new QueryExecutionException('failed_delete_by_ids:' . implode(',', $ids) . ":{$this->table}");
        }

        return (int) $result;
    }

    /**
     * Filter + sort + paginate over the table.
     *
     * @param array<string, mixed>|null $params
     * @return array<string, mixed>
     */
    public function search(?array $params): array
    {
        $queryParams = [];
        $whereClauses = $this->buildWhereClauses($params, $queryParams);
        $query = $this->buildSearchQuery($whereClauses, $params, $queryParams);

        $results = $this->wpdb->get_results($this->wpdb->prepare($query, $queryParams));
        $total = $this->getTotalCount($whereClauses, $queryParams);

        return [
            'data' => $results,
            'meta' => [
                'page'    => max((int) ($params['page'] ?? 1), 1),
                'perPage' => max((int) ($params['perPage'] ?? 10), 1),
                'total'   => $total,
            ],
        ];
    }

    /**
     * @param array<string, mixed>|null $params
     * @param array<int|string, mixed> $queryParams
     * @return string
     */
    protected function buildWhereClauses(?array $params, array &$queryParams): string
    {
        $whereClauses = ['1=1'];

        if (!empty($params['search']) && $this->getSearchableColumns()) {
            $searchEscaped = '%' . $this->wpdb->esc_like($params['search']) . '%';
            $conditions = [];
            foreach ($this->getSearchableColumns() as $col) {
                $conditions[] = "{$col} LIKE %s";
                $queryParams[] = $searchEscaped;
            }
            $whereClauses[] = '(' . implode(' OR ', $conditions) . ')';
        }

        if (!empty($params['filters'])) {
            foreach ($params['filters'] as $key => $value) {
                if (in_array($key, $this->getFilterableColumns(), true) && $value !== null && $value !== '') {
                    $whereClauses[] = "{$key} = %s";
                    $queryParams[] = sanitize_text_field($value);
                }
            }
        }

        return implode(' AND ', $whereClauses);
    }

    /**
     * @param string $whereClauses
     * @param array<string, mixed>|null $params
     * @param array<int|string, mixed> $queryParams
     * @return string
     */
    protected function buildSearchQuery(string $whereClauses, ?array $params, array &$queryParams): string
    {
        $sortable = $this->getSortableColumns();
        $sortBy = in_array($params['orderBy'] ?? 'id', $sortable, true) ? $params['orderBy'] : 'id';
        $order = strtolower($params['order'] ?? 'asc') === 'asc' ? 'ASC' : 'DESC';
        $perPage = max((int) ($params['perPage'] ?? 10), 1);
        $offset = (max((int) ($params['page'] ?? 1), 1) - 1) * $perPage;

        $queryParams[] = $perPage;
        $queryParams[] = $offset;

        return $this->selectQuery() . " WHERE {$whereClauses} ORDER BY {$sortBy} {$order} LIMIT %d OFFSET %d";
    }

    /**
     * @param string $whereClauses
     * @param array<int|string, mixed> $queryParams
     * @return int
     */
    protected function getTotalCount(string $whereClauses, array $queryParams): int
    {
        $countParams = count($queryParams) >= 2 ? array_slice($queryParams, 0, -2) : $queryParams;
        $countQuery = "SELECT COUNT(*) FROM {$this->table} WHERE {$whereClauses}";

        return (int) $this->wpdb->get_var($this->wpdb->prepare($countQuery, $countParams));
    }

    /** @return array<int, string> */
    protected function getSearchableColumns(): array
    {
        return [];
    }

    /** @return array<int, string> */
    protected function getFilterableColumns(): array
    {
        return [];
    }

    /** @return array<int, string> */
    protected function getSortableColumns(): array
    {
        return ['id'];
    }

    public function beginTransaction(): bool
    {
        return (bool) $this->wpdb->query('START TRANSACTION');
    }

    public function commit(): bool
    {
        return (bool) $this->wpdb->query('COMMIT');
    }

    public function rollback(): bool
    {
        return (bool) $this->wpdb->query('ROLLBACK');
    }
}
