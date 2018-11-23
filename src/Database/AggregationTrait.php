<?php

namespace Beast\Framework\Database;

trait AggregationTrait
{
    /**
     * Run a aggregate function against a column
     *
     * @param string
     * @param string
     * @param QueryBuilder
     * @return string
     */
    private function aggregate(string $method, string $column, QueryBuilder $query): string
    {
        $newQuery = clone $query;

        $newQuery->select(sprintf('%s(%s)', $method, $column));

        return $this->column($newQuery);
    }

    /**
     * Run count aggregate
     *
     * @param QueryBuilder
     * @param string|null
     * @return string
     */
    public function count(QueryBuilder $query, string $column = null): string
    {
        return $this->aggregate(__METHOD__, $column ?: $this->primary, $query);
    }

    /**
     * Run sum aggregate
     *
     * @param QueryBuilder
     * @param string|null
     * @return string
     */
    public function sum(QueryBuilder $query, string $column = null): string
    {
        return $this->aggregate(__METHOD__, $column ?: $this->primary, $query);
    }

    /**
     * Run min aggregate
     *
     * @param QueryBuilder
     * @param string|null
     * @return string
     */
    public function min(QueryBuilder $query, string $column = null): string
    {
        return $this->aggregate(__METHOD__, $column ?: $this->primary, $query);
    }

    /**
     * Run max aggregate
     *
     * @param QueryBuilder
     * @param string|null
     * @return string
     */
    public function max(QueryBuilder $query, string $column = null): string
    {
        return $this->aggregate(__METHOD__, $column ?: $this->primary, $query);
    }
}
