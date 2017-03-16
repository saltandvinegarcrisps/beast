<?php

namespace Beast\Framework\Commands;

trait MigrationTrait
{
    protected $connection;

    protected $paths;

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
}
