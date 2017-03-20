<?php

namespace Beast\Framework\Commands;

use Beast\Framework\Support\Paths;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\Connection;

class Migrations extends Command
{
    use MigrationTrait;

    use MigrationNamingTrait;

    public function __construct(Connection $connection, Paths $paths)
    {
        $this->connection = $connection;
        $this->paths = $paths;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('migrations:run')
            ->setDescription('Run migrations')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $migrations = $this->getMigrations();

        $last = $this->getLastRanMigration();
        $continue = $last ? false : true;

        foreach ($migrations as $info) {
            if ($continue) {
                $this->migrate($info['filename'], $info['classname']);
                $output->writeln($info['filename'] . ' <info>✔</info>');
            }
            else {
                $output->writeln($info['filename'] . ' ✔');
            }

            if ($info['filename'] == $last['filename']) {
                $continue = true;
            }
        }
    }

    protected function migrate($filename, $classname)
    {
        require $this->getMigrationFilepath($filename);

        $migration = new $classname();

        $sm = $this->connection->getSchemaManager();
        $fromSchema = $sm->createSchema();

        $toSchema = clone $fromSchema;
        $migration->up($toSchema);

        $queries = $fromSchema->getMigrateToSql($toSchema, $this->connection->getDatabasePlatform());

        foreach ($queries as $sql) {
            $this->connection->query($sql);
        }

        $this->connection->insert('migrations', [
            'filename' => $filename,
        ]);
    }
}
