<?php

namespace Beast\Framework\Database;

use Doctrine\DBAL\Query\QueryBuilder;

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
    private function aggregate(string $method, string $column, QueryBuilder $query = null): string
    {
        $newQuery = null === $query ? $this->getQueryBuilder() : clone $query;

        $newQuery->select(sprintf('%s(%s)', $method, $column));

        $value = $this->column($newQuery);

        // null or false if there are no more rows
        if (null === $value || false === $value) {
            return '';
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
        return $this->aggregate('count', $column ?: $this->primary, $query);
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
        return $this->aggregate('sum', $column ?: $this->primary, $query);
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
        return $this->aggregate('min', $column ?: $this->primary, $query);
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
        return $this->aggregate('max', $column ?: $this->primary, $query);
    }
}
