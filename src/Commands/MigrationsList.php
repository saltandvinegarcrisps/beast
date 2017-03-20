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
            ->setName('migrations:list')
            ->setDescription('List migrations')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $migrations = $this->getMigrations();

        $last = $this->getLastRanMigration();
        $continue = $last ? false : true;

        foreach ($migrations as $info) {
            if ($continue) {
                $output->writeln($info['filename'] . ' <error>✘</error>');
            }
            else {
                $output->writeln($info['filename'] . ' <info>✔</info>');
            }

            if ($info['filename'] == $last['filename']) {
                $continue = true;
            }
        }
    }
}
