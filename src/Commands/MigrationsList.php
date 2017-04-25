<?php

namespace Beast\Framework\Commands;

use Beast\Framework\Support\Paths;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\Connection;

class MigrationsList extends Command
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
            ->setName('migrations:list')
            ->setDescription('List migrations')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $migrations = $this->getMigrations();
        $installed = $this->getRanMigrations();

        foreach ($migrations as $migration) {
            $migrated = array_reduce($installed, function ($carry, $item) use ($migration) {
                return $migration['filename'] == $item['filename'] ? true : $carry;
            }, false);

            if (! $migrated) {
                $output->writeln($migration['name'] . ' <error>✘</error>');
            } else {
                $output->writeln($migration['name'] . ' <info>✔</info>');
            }
        }
    }
}
