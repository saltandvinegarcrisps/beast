<?php

namespace Beast\Framework\Tests;

use Beast\Framework\Database\EntityInterface;
use Beast\Framework\Database\TableGateway;
use Beast\Framework\Database\TableGatewayException;
use PHPUnit\Framework\TestCase;

class TableGatewayTest extends TestCase
{
    protected $conn;

    protected function setUp(): void
    {
        $config = new \Doctrine\DBAL\Configuration();

        $this->conn = \Doctrine\DBAL\DriverManager::getConnection([
            'url' => 'sqlite:///:memory:',
        ], $config);

        $this->conn->executeQuery('create table if not exists employees(id int, name text)');
        $this->conn->executeQuery('create table if not exists absences(id int, employee_id int, start_date text, end_date text)');
    }

    public function testMissingTableName(): void
    {
        $this->expectException(TableGatewayException::class);
        $table = new class($this->conn) extends TableGateway {
            protected $primary = 'id';
        };
    }

    public function testTableName(): void
    {
        $table = new class($this->conn) extends TableGateway {
            protected $table = 'employees';
            protected $primary = 'id';
        };

        $this->assertEquals('employees', $table->getTable());
    }

    public function testMissingTablePrimaryKeyName(): void
    {
        $this->expectException(TableGatewayException::class);
        $$table = new class($this->conn) extends TableGateway {
            protected $table = 'employees';
        };
    }

    public function testTablePrimaryKeyName(): void
    {
        $table = new class($this->conn) extends TableGateway {
            protected $table = 'employees';
            protected $primary = 'id';
        };

        $this->assertEquals('id', $table->getPrimary());
    }

    public function testTablePrototype(): void
    {
        $table = new class($this->conn) extends TableGateway {
            protected $table = 'employees';
            protected $primary = 'id';
        };

        $this->assertTrue($table->getPrototype() instanceof EntityInterface);
    }

    public function testTableConnection(): void
    {
        $table = new class($this->conn) extends TableGateway {
            protected $table = 'employees';
            protected $primary = 'id';
        };

        $this->assertEquals($this->conn, $table->getConnection());
    }

    public function testTableQueryBuilder(): void
    {
        $table = new class($this->conn) extends TableGateway {
            protected $table = 'employees';
            protected $primary = 'id';
        };

        $qb = $table->getQueryBuilder();

        $this->assertTrue($qb instanceof \Doctrine\DBAL\Query\QueryBuilder);
        $this->assertEquals('*', $qb->getQueryPart('select')[0]);
        $this->assertEquals('employees', $qb->getQueryPart('from')[0]['table']);
    }

    public function testTableInsert(): void
    {
        $table = new class($this->conn) extends TableGateway {
            protected $table = 'employees';
            protected $primary = 'id';
        };

        $result = $table->insert([
            'id' => 1,
            'name' => 'Bob',
        ]);

        $this->assertEquals(1, $result);
    }

    public function testTableFetchException(): void
    {
        $table = new class($this->conn) extends TableGateway {
            protected $table = 'employees';
            protected $primary = 'id';
        };

        $this->expectException(TableGatewayException::class);
        $table->fetch($table->getQueryBuilder()->select('fail()'));
    }

    public function testTableFetch(): void
    {
        $table = new class($this->conn) extends TableGateway {
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

    public function testTableGet(): void
    {
        $table = new class($this->conn) extends TableGateway {
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

    public function testTableGetUnbuffered(): void
    {
        $table = new class($this->conn) extends TableGateway {
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

    public function testTableColumn(): void
    {
        $table = new class($this->conn) extends TableGateway {
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

    public function testTableCount(): void
    {
        $employees = new class($this->conn) extends TableGateway {
            protected $table = 'employees';
            protected $primary = 'id';
        };

        $total = $employees->count();
        $this->assertEquals('0', $total);

        $employees->insert([
            'id' => 1,
            'name' => 'Bob',
        ]);

        $total = $employees->count();
        $this->assertEquals('1', $total);

        // ---

        $absences = new class($this->conn) extends TableGateway {
            protected $table = 'absences';
            protected $primary = 'id';
        };

        $absences->insert([
            'id' => 1,
            'employee_id' => 1,
            'start_date' => '2000-01-01',
            'end_date' => '2000-01-11',
        ]);

        $absences->insert([
            'id' => 2,
            'employee_id' => 1,
            'start_date' => '2000-03-01',
            'end_date' => '2000-03-11',
        ]);

        // ---

        $query = $employees->getQueryBuilder()->join('employees', 'absences', 'a', 'a.employee_id = employees.id');
        $total = $employees->count($query);
        $this->assertEquals('2', $total);
    }

    public function testTableSum(): void
    {
        $table = new class($this->conn) extends TableGateway {
            protected $table = 'employees';
            protected $primary = 'id';
        };

        $total = $table->sum();
        $this->assertEquals('0', $total);

        $table->insert([
            'id' => 1,
            'name' => 'Bob',
        ]);

        $total = $table->sum();
        $this->assertEquals('1', $total);
    }

    public function testTableMin(): void
    {
        $table = new class($this->conn) extends TableGateway {
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

    public function testTableMax(): void
    {
        $table = new class($this->conn) extends TableGateway {
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

    public function testTableUpdateException(): void
    {
        $table = new class($this->conn) extends TableGateway {
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

    public function testTableUpdate(): void
    {
        $table = new class($this->conn) extends TableGateway {
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

    public function testTableDelete(): void
    {
        $table = new class($this->conn) extends TableGateway {
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
