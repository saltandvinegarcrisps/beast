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

    protected function toCamelCase(string $str): string
    {
        $separators = [' ', '_', '-'];

        $str = ucwords($str, implode('', $separators));

        return str_replace($separators, '', $str);
    }

    protected function toSnakeCase(string $str): string
    {
        $str = strtolower($str);

        return str_replace(' ', '_', $str);
    }

    protected function extract($fileinfo): array
    {
        $basename = $fileinfo->getBasename();

        list($year, $month, $day, $hour, $min, $sec, $name) = sscanf($basename, '%4d_%2d_%2d_%2d%2d%2d_%s');

        return [
            'date' => new \DateTime(sprintf('%d-%d-%d %d:%d:%d', $year, $month, $day, $hour, $min, $sec)),
            'filename' => $basename,
            'name' => $name,
            'classname' => $this->toCamelCase($name),
        ];
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
            if ($a['date'] == $b['date']) {
                return 0;
            }

            return $a['date'] > $b['date'] ? 1 : -1;
        });

        return $files;
    }
}
