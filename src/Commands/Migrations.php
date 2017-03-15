<?php

namespace Beast\Framework\Commands;

use Beast\Framework\Paths;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\Connection;

class Migrations extends Command
{
    protected $connection;

    protected $paths;

    public function __construct(Connection $connection, Paths $paths)
    {
        $this->connection = $connection;
        $this->paths = $paths;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('migrate')
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
                $output->writeln('<info>'.$info['filename'].'</info>');
                $this->migrate($info['filename'], $info['classname']);
            }

            if ($info['filename'] == $last['filename']) {
                $continue = true;
            }
        }
    }

    protected function getLastRanMigration()
    {
        $stmt = $this->connection->query('SELECT * FROM migrations ORDER BY id DESC');

        return $stmt->fetch() ?: false;
    }

    protected function getMigrationsPath(): string
    {
        return $this->paths->resolve('app/migrations');
    }

    protected function getMigrationFilepath(string $filename): string
    {
        return sprintf('%s/%s.php', $this->getMigrationsPath(), $filename);
    }

    protected function getMigrations(): array
    {
        $fi = new \FilesystemIterator($this->getMigrationsPath(), \FilesystemIterator::SKIP_DOTS);

        $files = [];

        foreach ($fi as $fileinfo) {
            if ($fileinfo->getExtension() == 'php') {
                $files[] = $this->extract($fileinfo);
            }
        }

        usort($files, function ($a, $b) {
            if ($a['time'] == $b['time']) {
                return 0;
            }

            return $a['time'] > $b['time'] ? 1 : -1;
        });

        return $files;
    }

    protected function extract(string $filepath): array
    {
        $filename = pathinfo($filepath, PATHINFO_FILENAME);
        $parts = explode('_', $filename);

        return [
            'filename' => $filename,
            'time' => array_pop($parts),
            'classname' => str_replace(' ', '', ucwords(implode(' ', $parts))),
        ];
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
