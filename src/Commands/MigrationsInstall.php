<?php

namespace Beast\Framework\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\Connection;

class MigrationsInstall extends Command
{
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('migrations:install')
            ->setDescription('Create migrations table')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sm = $this->connection->getSchemaManager();
        $fromSchema = $sm->createSchema();

        $toSchema = clone $fromSchema;
        $table = $toSchema->createTable('migrations');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('filename', 'text');
        $table->setPrimaryKey(['id']);

        $queries = $fromSchema->getMigrateToSql($toSchema, $this->connection->getDatabasePlatform());

        foreach ($queries as $sql) {
            $this->connection->query($sql);
        }
    }
}
