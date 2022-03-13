<?php

namespace Beast\Framework\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\Query\QueryBuilder;
use Generator;

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
        if ($this->model) {
            $prototype = new $this->model;
        }

        // create anon object class to create rows from
        if ($prototype === null) {
            $prototype = new class extends Entity {};
        }

        if (!$prototype instanceof EntityInterface) {
            throw new \LogicException('`$prototype` must be an instance of EntityInterface');
        }

        $this->prototype = $prototype;

        if (empty($this->table)) {
            throw new TableGatewayException(sprintf(
                'The property "table" must be set on class %s',
                \get_class($this)
            ));
        }

        if (empty($this->primary)) {
            throw new TableGatewayException(sprintf(
                'The property "primary" must be set on class %s',
                \get_class($this)
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
     * Get active database connection
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
        return $this->getConnection()->createQueryBuilder()->select('*')->from($this->table);
    }

    /**
     * Execute a query against this table gateway
     *
     * @param QueryBuilder $query
     * @return \Doctrine\DBAL\ForwardCompatibility\DriverStatement<int, array<string, int|string|null>>|\Doctrine\DBAL\ForwardCompatibility\DriverResultStatement<int, array<string, int|string|null>>
     * @throws TableGatewayException
     */
    protected function execute(QueryBuilder $query)
    {
        try {
            return $this->getConnection()->executeQuery($query->getSQL(), $query->getParameters());
        } catch (DriverException $e) {
            throw new TableGatewayException('There was error executing query', 0, $e);
        }
    }

    /**
     * Create a model entity from database row
     *
     * @param array<string, int|string|null> $attributes
     * @return EntityInterface
     */
    protected function model(array $attributes): EntityInterface
    {
        return (clone $this->getPrototype())->withAttributes($attributes);
    }

    /**
     * Fetch the first row from a query
     *
     * @param QueryBuilder|null $query
     * @return EntityInterface|null
     */
    public function fetch(QueryBuilder $query = null): ?EntityInterface
    {
        if (null === $query) {
            $query = $this->getQueryBuilder();
        }

        $statement = $this->execute($query);

        /** @var array<string, int|string|null> $row */
        $row = $statement->fetchAssociative();

        if ($row) {
            return $this->model($row);
        }

        return null;
    }

    /**
     * Get memory limit in bytes
     *
     * @return int
     */
    protected function getMemoryLimit(): int
    {
        $limit = \ini_get('memory_limit');
        if ($limit === '-1' || empty($limit)) {
            return PHP_INT_MAX;
        }
        return $this->toBytes($limit);
    }

    /**
     * Convert binary size string (512M) into bytes
     *
     * @param string $string
     * @return int
     */
    protected function toBytes(string $string): int
    {
        sscanf($string, '%u%c', $number, $suffix);

        if (isset($suffix)) {
            $exp = strpos(' KMG', strtoupper($suffix));
            if (false !== $exp) {
                $number = $number * pow(1024, $exp);
            }
        }

        return $number;
    }

    /**
     * Get array of entities from the query
     *
     * @param QueryBuilder|null $query
     * @return array<EntityInterface>
     */
    public function get(QueryBuilder $query = null): array
    {
        if (null === $query) {
            $query = $this->getQueryBuilder();
        }

        $statement = $this->execute($query);
        $results = [];

        // when buffering results into array
        // check memory usage to prevent fatal error
        $limit = $this->getMemoryLimit();
        $current = memory_get_usage();
        $max = $limit - $current;

        foreach ($statement as $row) {
            $results[] = $this->model($row);
            $usage = memory_get_usage();
            if ($usage > $max) {
                throw new \OverflowException(sprintf('Allowed memory size of %s bytes has been exceeded', $max));
            }
        }

        return $results;
    }

    /**
     * Get unbuffered array of entities from the query
     *
     * @param QueryBuilder|null $query
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
     * @param QueryBuilder $query
     * @return mixed
     */
    public function column(QueryBuilder $query = null)
    {
        if (null === $query) {
            $query = $this->getQueryBuilder();
        }

        return $this->getConnection()->fetchColumn($query->getSQL(), $query->getParameters());
    }

    /**
     * Insert array of data returning the insert id
     *
     * @param non-empty-array<string, mixed> $params
     * @return numeric-string
     */
    public function insert(array $params): string
    {
        if ($this->getConnection()->insert($this->table, $params)) {
            $platform = $this->getConnection()->getDatabasePlatform();
            $sequenceName = $platform->supportsSequences() ?
                $platform->getIdentitySequenceName($this->table, $this->primary) :
                null;

            $lastInsertId = $this->getConnection()->lastInsertId($sequenceName);

            return $lastInsertId === false || !is_numeric($lastInsertId) ? '0' : (string) $lastInsertId;
        }

        return '0';
    }

    /**
     * Update from query returning the number of row affected
     *
     * @param QueryBuilder $query
     * @return int
     */
    public function update(QueryBuilder $query): int
    {
        $query->update($this->table);

        try {
            return (int) $this->getConnection()->executeUpdate($query->getSQL(), $query->getParameters());
        } catch (DriverException $e) {
            throw new TableGatewayException('There was error executing update', 0, $e);
        }
    }

    /**
     * Delete rows from table gateway using query
     * returning the number of row affected
     *
     * @param QueryBuilder $query
     * @return int
     */
    public function delete(QueryBuilder $query): int
    {
        $query->delete($this->table);

        return (int) $this->execute($query)->rowCount();
    }
}
