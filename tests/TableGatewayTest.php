<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Beast\Framework\Database\TableGateway;
use Beast\Framework\Database\EntityInterface;
use Beast\Framework\Exceptions\TableGatewayException;

class TableGatewayTest extends TestCase
{
    protected $conn;

    protected function setUp()
    {
        $config = new \Doctrine\DBAL\Configuration();

        $this->conn = \Doctrine\DBAL\DriverManager::getConnection([
            'url' => 'sqlite:///:memory:',
        ], $config);

        $this->conn->executeQuery('create table if not exists employees(id int, name text)');
    }

    public function testMissingTableName()
    {
        $this->expectException(TableGatewayException::class);
        $table = new class($this->conn) extends TableGateway
        {
            protected $primary = 'id';
        };
    }

    public function testTableName()
    {
        $table = new class($this->conn) extends TableGateway
        {
            protected $table = 'employees';
            protected $primary = 'id';
        };

        $this->assertEquals('employees', $table->getTable());
    }

    public function testMissingTablePrimaryKeyName()
    {
        $this->expectException(TableGatewayException::class);
        $$table = new class($this->conn) extends TableGateway
        {
            protected $table = 'employees';
        };
    }

    public function testTablePrimaryKeyName()
    {
        $table = new class($this->conn) extends TableGateway
        {
            protected $table = 'employees';
            protected $primary = 'id';
        };

        $this->assertEquals('id', $table->getPrimary());
    }

    public function testTablePrototype()
    {
        $table = new class($this->conn) extends TableGateway
        {
            protected $table = 'employees';
            protected $primary = 'id';
        };

        $this->assertTrue($table->getPrototype() instanceof EntityInterface);
    }

    public function testTableConnection()
    {
        $table = new class($this->conn) extends TableGateway
        {
            protected $table = 'employees';
            protected $primary = 'id';
        };

        $this->assertEquals($this->conn, $table->getConnection());
    }

    public function testTableQueryBuilder()
    {
        $table = new class($this->conn) extends TableGateway
        {
            protected $table = 'employees';
            protected $primary = 'id';
        };

        $qb = $table->getQueryBuilder();

        $this->assertTrue($qb instanceof \Doctrine\DBAL\Query\QueryBuilder);
        $this->assertEquals('*', $qb->getQueryPart('select')[0]);
        $this->assertEquals('employees', $qb->getQueryPart('from')[0]['table']);
    }

    public function testTableInsert()
    {
        $table = new class($this->conn) extends TableGateway
        {
            protected $table = 'employees';
            protected $primary = 'id';
        };

        $result = $table->insert([
            'id' => 1,
            'name' => 'Bob',
        ]);

        $this->assertEquals(1, $result);
    }

    public function testTableFetchException()
    {
        $table = new class($this->conn) extends TableGateway
        {
            protected $table = 'employees';
            protected $primary = 'id';
        };

        $this->expectException(TableGatewayException::class);
        $table->fetch($table->getQueryBuilder()->select('fail()'));
    }

    public function testTableFetch()
    {
        $table = new class($this->conn) extends TableGateway
        {
            protected $table = 'employees';
            protected $primary = 'id';
        };

        // empty table
        $this->assertNull($table->fetch());

        $table->insert([
            'id' => 1,
            'name' => 'Bob',
        ]);

        $model = $table->fetch();
        $this->assertTrue($model instanceof EntityInterface);
        $this->assertEquals('Bob', $model->name);
    }

    public function testTableGet()
    {
        $table = new class($this->conn) extends TableGateway
        {
            protected $table = 'employees';
            protected $primary = 'id';
        };

        // empty table
        $this->assertEmpty($table->get());

        $table->insert([
            'id' => 1,
            'name' => 'Bob',
        ]);

        $results = $table->get();
        $this->assertCount(1, $results);

        $table->insert([
            'id' => 2,
            'name' => 'Steve',
        ]);

        $results = $table->get();
        $this->assertCount(2, $results);
    }

    public function testTableGetUnbuffered()
    {
        $table = new class($this->conn) extends TableGateway
        {
            protected $table = 'employees';
            protected $primary = 'id';
        };

        // empty table
        $this->assertEmpty($table->getUnbuffered()->current());

        $table->insert([
            'id' => 1,
            'name' => 'Bob',
        ]);

        $results = $table->getUnbuffered();
        $this->assertEquals('Bob', $results->current()->name);

        $table->insert([
            'id' => 2,
            'name' => 'Steve',
        ]);

        $results = $table->getUnbuffered();
        $this->assertEquals('Bob', $results->current()->name);
        $results->next();
        $this->assertEquals('Steve', $results->current()->name);
    }

    public function testTableColumn()
    {
        $table = new class($this->conn) extends TableGateway
        {
            protected $table = 'employees';
            protected $primary = 'id';
        };

        $table->insert([
            'id' => 1,
            'name' => 'Bob',
        ]);

        $id = $table->column();
        $this->assertEquals('1', $id);
    }

    public function testTableCount()
    {
        $table = new class($this->conn) extends TableGateway
        {
            protected $table = 'employees';
            protected $primary = 'id';
        };

        $table->insert([
            'id' => 1,
            'name' => 'Bob',
        ]);

        $total = $table->count();
        $this->assertEquals('1', $total);
    }

    public function testTableSum()
    {
        $table = new class($this->conn) extends TableGateway
        {
            protected $table = 'employees';
            protected $primary = 'id';
        };

        $table->insert([
            'id' => 1,
            'name' => 'Bob',
        ]);

        $total = $table->sum();
        $this->assertEquals('1', $total);
    }

    public function testTableMin()
    {
        $table = new class($this->conn) extends TableGateway
        {
            protected $table = 'employees';
            protected $primary = 'id';
        };

        $table->insert([
            'id' => 1,
            'name' => 'Bob',
        ]);

        $total = $table->min();
        $this->assertEquals('1', $total);
    }

    public function testTableMax()
    {
        $table = new class($this->conn) extends TableGateway
        {
            protected $table = 'employees';
            protected $primary = 'id';
        };

        $table->insert([
            'id' => 1,
            'name' => 'Bob',
        ]);

        $table->insert([
            'id' => 2,
            'name' => 'Steve',
        ]);

        $total = $table->max();
        $this->assertEquals('2', $total);
    }

    public function testTableUpdateException()
    {
        $table = new class($this->conn) extends TableGateway
        {
            protected $table = 'employees';
            protected $primary = 'id';
        };

        $table->insert([
            'id' => 1,
            'name' => 'Bob',
        ]);

        $this->expectException(TableGatewayException::class);
        $query = $table->getQueryBuilder()
            ->set('name_fail', ':name')
            ->setParameter('name', 'Sharon')
            ->where('id = :id')
            ->setParameter('id', 1)
        ;
        $table->update($query);
    }

    public function testTableUpdate()
    {
        $table = new class($this->conn) extends TableGateway
        {
            protected $table = 'employees';
            protected $primary = 'id';
        };

        $table->insert([
            'id' => 1,
            'name' => 'Bob',
        ]);

        $query = $table->getQueryBuilder()
            ->set('name', ':name')
            ->setParameter('name', 'Sharon')
            ->where('id = :id')
            ->setParameter('id', 1)
        ;
        $table->update($query);

        $name = $table->column($table->getQueryBuilder()->select('name'));
        $this->assertEquals('Sharon', $name);
    }

    public function testTableDelete()
    {
        $table = new class($this->conn) extends TableGateway
        {
            protected $table = 'employees';
            protected $primary = 'id';
        };

        $table->insert([
            'id' => 1,
            'name' => 'Bob',
        ]);

        $table->insert([
            'id' => 2,
            'name' => 'Steve',
        ]);

        $query = $table->getQueryBuilder()
            ->where('id = :id')
            ->setParameter('id', 1)
        ;
        $table->delete($query);

        $name = $table->column($table->getQueryBuilder()->select('name'));
        $this->assertEquals('Steve', $name);
    }
}
