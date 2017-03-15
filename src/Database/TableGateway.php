<?php

namespace Beast\Framework\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

abstract class TableGateway
{
    protected $db;

    protected $prototype;

    protected $table;

    protected $primary;

    public function __construct(Connection $db, Entity $prototype)
    {
        $this->db = $db;
        $this->prototype = $prototype;
    }

    public function getPrototype(): Entity
    {
        return $this->prototype;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->db->createQueryBuilder()->select('*')->from($this->table);
    }

    public function all(): array
    {
        return $this->get($this->getQueryBuilder());
    }

    public function fetch(QueryBuilder $query)
    {
        $statement = $this->db->executeQuery($query->getSQL(), $query->getParameters());
        if ($row = $statement->fetch()) {
            return (clone $this->prototype)->withAttributes($row);
        }
        return false;
    }

    public function get(QueryBuilder $query): array
    {
        $statement = $this->db->executeQuery($query->getSQL(), $query->getParameters());
        $results = [];

        foreach ($statement as $row) {
            $results[] = (clone $this->prototype)->withAttributes($row);
        }

        return $results;
    }

    public function column(QueryBuilder $query)
    {
        return $this->db->fetchColumn($query->getSQL(), $query->getParameters());
    }

    public function count(QueryBuilder $query): int
    {
        return $this->column($query->select('COUNT(*)'));
    }

    public function insert(array $params): int
    {
        if ($this->db->insert($this->table, $params)) {
            return $this->db->lastInsertId();
        }

        return 0;
    }

    public function update(QueryBuilder $query): int
    {
        $query->update($this->table);

        return $this->db->executeUpdate($query->getSQL(), $query->getParameters());
    }

    public function delete(QueryBuilder $query): int
    {
        $query->delete($this->table);

        $statement = $this->db->executeQuery($query->getSQL(), $query->getParameters());

        return $statement->rowCount();
    }
}
