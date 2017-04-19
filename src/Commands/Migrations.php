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
        $installed = $this->getRanMigrations();

        foreach ($migrations as $migration) {
            $migrated = array_reduce($installed, function($carry, $item) use($migration) {
                return $migration['filename'] == $item['filename'] ? true : $carry;
            }, false);

            if(! $migrated) {
                $this->migrate($migration['filename'], $migration['classname'], $output);
                $output->writeln($migration['filename'] . ' <info>✔</info>');
            }
            else {
                $output->writeln($migration['filename'] . ' ✔');
            }
        }
    }

    protected function migrate(string $filename, string $classname, OutputInterface $output)
    {
        require $this->getMigrationFilepath($filename);

        $migration = new $classname();

        $sm = $this->connection->getSchemaManager();
        $fromSchema = $sm->createSchema();

        $toSchema = clone $fromSchema;
        $migration->migrate($toSchema);

        $queries = $fromSchema->getMigrateToSql($toSchema, $this->connection->getDatabasePlatform());

        foreach ($queries as $sql) {
            $output->writeln($sql);
            $this->connection->query($sql);
        }

        $this->connection->insert('migrations', [
            'filename' => $filename,
        ]);
    }
}
