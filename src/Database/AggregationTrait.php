<?php

namespace Beast\Framework\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

trait AggregationTrait
{
    /**
     * Get active database connection
     *
     * @return Connection
     */
    abstract public function getConnection(): Connection;

    /**
     * Get this table gateway query builder
     *
     * @return QueryBuilder
     */
    abstract public function getQueryBuilder(): QueryBuilder;

    /**
     * Fetch the first column from the first row of a query
     *
     * @param QueryBuilder|null $query
     * @return string|int|null|boolean
     */
    abstract public function column(QueryBuilder $query = null);

    /**
     * Run a aggregate function against a column
     *
     * @param string $method
     * @param string|null $column
     * @param QueryBuilder|null $query
     * @return string
     */
    private function aggregate(string $method, string $column = null, QueryBuilder $query = null): string
    {
        $newQuery = null === $query ? $this->getQueryBuilder() : clone $query;

        if (null === $column) {
            $column = sprintf(
                '%s.%s',
                $this->getConnection()->quoteIdentifier($this->table),
                $this->getConnection()->quoteIdentifier($this->primary)
            );
        }

        $newQuery->select(sprintf('%s(%s)', $method, $column));

        $value = $this->column($newQuery);

        // null or false if there are no more rows
        if (null === $value || false === $value) {
            return '0';
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        throw new \UnexpectedValueException('`column` did not return a numeric-string or integer');
    }

    /**
     * Run count aggregate
     *
     * @param QueryBuilder|null $query
     * @param string|null $column
     * @return string
     */
    public function count(QueryBuilder $query = null, string $column = null): string
    {
        return $this->aggregate('count', $column, $query);
    }

    /**
     * Run sum aggregate
     *
     * @param QueryBuilder|null $query
     * @param string|null $column
     * @return string
     */
    public function sum(QueryBuilder $query = null, string $column = null): string
    {
        return $this->aggregate('sum', $column, $query);
    }

    /**
     * Run min aggregate
     *
     * @param QueryBuilder|null $query
     * @param string|null $column
     * @return string
     */
    public function min(QueryBuilder $query = null, string $column = null): string
    {
        return $this->aggregate('min', $column, $query);
    }

    /**
     * Run max aggregate
     *
     * @param QueryBuilder|null $query
     * @param string|null $column
     * @return string
     */
    public function max(QueryBuilder $query = null, string $column = null): string
    {
        return $this->aggregate('max', $column, $query);
    }
}
