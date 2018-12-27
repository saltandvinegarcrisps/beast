<?php

namespace Beast\Framework\Database;

use Generator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Driver\PDOStatement;
use Doctrine\DBAL\Exception\DriverException;

abstract class TableGateway
{
    use AggregationTrait;

    /**
     * The database connection
     * @var Connection
     */
    protected $conn;

    /**
     * The entity prototype to clone for each row
     * @var EntityInterface
     */
    protected $prototype;

    /**
     * The class name of the model to prototype
     * @var string
     */
    protected $model;

    /**
     * The table name
     * @var string
     */
    protected $table;

    /**
     * The primary key name
     * @var string
     */
    protected $primary;

    public function __construct(Connection $conn, EntityInterface $prototype = null)
    {
        $this->conn = $conn;

        // create prototype from model class name if one was set
        if (is_string($this->model)) {
            $prototype = new $this->model;
        }

        // create anon object class to create rows from
        if ($prototype === null) {
            $prototype = new class extends Entity {
            };
        }

        $this->prototype = $prototype;

        if (empty($this->table)) {
            throw new TableGatewayException(sprintf(
                'The property "table" must be set on class %s',
                get_class($this)
            ));
        }

        if (empty($this->primary)) {
            throw new TableGatewayException(sprintf(
                'The property "primary" must be set on class %s',
                get_class($this)
            ));
        }
    }

    /**
     * Get the table name
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Get name of primary key
     *
     * @return string
     */
    public function getPrimary(): string
    {
        return $this->primary;
    }

    /**
     * Get entity prototype to clone
     *
     * @return EntityInterface
     */
    public function getPrototype(): EntityInterface
    {
        return $this->prototype;
    }

    /**
     * Get database connection
     *
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->conn;
    }

    /**
     * Get this table gateway query builder
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder(): QueryBuilder
    {
        return $this->conn->createQueryBuilder()->select('*')->from($this->table);
    }

    /**
     * Execute a query against this table gateway
     *
     * @param QueryBuilder
     * @return PDOStatement
     * @throws TableGatewayException
     */
    protected function execute(QueryBuilder $query): PDOStatement
    {
        try {
            return $this->conn->executeQuery($query->getSQL(), $query->getParameters());
        } catch (DriverException $e) {
            throw new TableGatewayException('There was error executing query', 0, $e);
        }
    }

    /**
     * Create a model entity from database row
     *
     * @param array
     * @return EntityInterface
     */
    protected function model(array $attributes): EntityInterface
    {
        return (clone $this->getPrototype())->withAttributes($attributes);
    }

    /**
     * Fetch the first row from a query
     *
     * @param QueryBuilder|null
     * @return EntityInterface|null
     */
    public function fetch(QueryBuilder $query = null): ?EntityInterface
    {
        if (null === $query) {
            $query = $this->getQueryBuilder();
        }

        $statement = $this->execute($query);

        if ($row = $statement->fetch()) {
            return $this->model($row);
        }

        return null;
    }

    /**
     * Get array of entities from the query
     *
     * @param QueryBuilder|null
     * @return array
     */
    public function get(QueryBuilder $query = null): array
    {
        if (null === $query) {
            $query = $this->getQueryBuilder();
        }

        $statement = $this->execute($query);
        $results = [];

        foreach ($statement as $row) {
            $results[] = $this->model($row);
        }

        return $results;
    }

    /**
     * Get unbuffered array of entities from the query
     *
     * @param QueryBuilder|null
     * @return Generator
     */
    public function getUnbuffered(QueryBuilder $query = null): Generator
    {
        if (null === $query) {
            $query = $this->getQueryBuilder();
        }

        $statement = $this->execute($query);

        foreach ($statement as $row) {
            yield $this->model($row);
        }
    }

    /**
     * Fetch the first column from the first row of a query
     *
     * @param QueryBuilder
     * @return mixed
     */
    public function column(QueryBuilder $query = null)
    {
        if (null === $query) {
            $query = $this->getQueryBuilder();
        }

        return $this->conn->fetchColumn($query->getSQL(), $query->getParameters());
    }

    /**
     * Insert array of data returning the insert id
     *
     * @param array
     * @return string
     */
    public function insert(array $params): string
    {
        if ($this->conn->insert($this->table, $params)) {
            $platform = $this->conn->getDatabasePlatform();
            $sequenceName = $platform->supportsSequences() ?
                $platform->getIdentitySequenceName($this->table, $this->primary) :
                null;
            return $this->conn->lastInsertId($sequenceName);
        }

        return '0';
    }

    /**
     * Update from query returning the number of row affected
     *
     * @param QueryBuilder
     * @return int
     */
    public function update(QueryBuilder $query): int
    {
        $query->update($this->table);

        try {
            return $this->conn->executeUpdate($query->getSQL(), $query->getParameters());
        } catch (DriverException $e) {
            throw new TableGatewayException('There was error executing update', 0, $e);
        }
    }

    /**
     * Delete rows from table gateway using query
     * returning the number of row affected
     *
     * @param QueryBuilder
     * @return int
     */
    public function delete(QueryBuilder $query): int
    {
        $query->delete($this->table);

        $statement = $this->execute($query);

        return $statement->rowCount();
    }
}
