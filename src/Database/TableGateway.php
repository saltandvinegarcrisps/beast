<?php

namespace Beast\Framework\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Driver\PDOStatement;

abstract class TableGateway
{
    protected $db;

    protected $prototype;

    protected $table;

    protected $primary;

    public function __construct(Connection $db, EntityInterface $prototype = null)
    {
        $this->db = $db;

        if ($prototype === null) {
            $prototype = new class extends Entity {
            };
        }

        $this->prototype = $prototype;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getPrimary(): string
    {
        return $this->primary;
    }

    public function getPrototype(): EntityInterface
    {
        return $this->prototype;
    }

    public function getConnection(): Connection
    {
        return $this->db;
    }

    public function getDefaults(): array
    {
        throw new \ErrorException('getDefaults is depreciated.');
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->db->createQueryBuilder()->select('*')->from($this->table);
    }

    public function execute(QueryBuilder $query): PDOStatement
    {
        return $this->db->executeQuery($query->getSQL(), $query->getParameters());
    }

    public function model(array $attributes): EntityInterface
    {
        return (clone $this->getPrototype())->withAttributes($attributes);
    }

    public function fetch(QueryBuilder $query)
    {
        $statement = $this->execute($query);

        if ($row = $statement->fetch()) {
            return $this->model($row);
        }

        return false;
    }

    public function get(QueryBuilder $query): array
    {
        $statement = $this->execute($query);
        $results = [];

        foreach ($statement as $row) {
            $results[] = $this->model($row);
        }

        return $results;
    }

    public function all(): array
    {
        $query = $this->getQueryBuilder();

        return $this->get($query);
    }

    public function column(QueryBuilder $query)
    {
        return $this->db->fetchColumn($query->getSQL(), $query->getParameters());
    }

    public function count(QueryBuilder $query): int
    {
        return $this->column((clone $query)->select('COUNT(*)'));
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

        $statement = $this->execute($query);

        return $statement->rowCount();
    }
}
