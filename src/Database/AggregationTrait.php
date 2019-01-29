<?php

namespace Beast\Framework\Database;

use Doctrine\DBAL\Query\QueryBuilder;

trait AggregationTrait
{
    /**
     * Get this table gateway query builder
     *
     * @return QueryBuilder
     */
    abstract public function getQueryBuilder(): QueryBuilder;

    /**
     * Fetch the first column from the first row of a query
     *
     * @param QueryBuilder
     * @return mixed
     */
    abstract public function column(QueryBuilder $query = null);

    /**
     * Run a aggregate function against a column
     *
     * @param string
     * @param string
     * @param QueryBuilder
     * @return string
     */
    private function aggregate(string $method, string $column = null, QueryBuilder $query = null): string
    {
        $newQuery = null === $query ? $this->getQueryBuilder() : clone $query;

        if (null === $column) {
            $column = \sprintf(
                '%s.%s',
                $this->conn->quoteIdentifier($this->table),
                $this->conn->quoteIdentifier($this->primary)
            );
        }

        $newQuery->select(\sprintf('%s(%s)', $method, $column));

        $value = $this->column($newQuery);

        // null or false if there are no more rows
        if (null === $value || false === $value) {
            return '0';
        }

        return $value;
    }

    /**
     * Run count aggregate
     *
     * @param QueryBuilder
     * @param string|null
     * @return string
     */
    public function count(QueryBuilder $query = null, string $column = null): string
    {
        return $this->aggregate('count', $column, $query);
    }

    /**
     * Run sum aggregate
     *
     * @param QueryBuilder
     * @param string|null
     * @return string
     */
    public function sum(QueryBuilder $query = null, string $column = null): string
    {
        return $this->aggregate('sum', $column, $query);
    }

    /**
     * Run min aggregate
     *
     * @param QueryBuilder
     * @param string|null
     * @return string
     */
    public function min(QueryBuilder $query = null, string $column = null): string
    {
        return $this->aggregate('min', $column, $query);
    }

    /**
     * Run max aggregate
     *
     * @param QueryBuilder
     * @param string|null
     * @return string
     */
    public function max(QueryBuilder $query = null, string $column = null): string
    {
        return $this->aggregate('max', $column, $query);
    }
}
