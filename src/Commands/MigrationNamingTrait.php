<?php

namespace Beast\Framework\Commands;

trait MigrationNamingTrait
{
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

    protected function extract(string $filepath): array
    {
        $filename = pathinfo($filepath, PATHINFO_FILENAME);
        $needle = strrpos($filename, '_');
        $name = substr($filename, 0, $needle);
        $time = substr($filename, $needle + 1);

        return [
            'filename' => $filename,
            'time' => $time,
            'classname' => $this->toCamelCase($name),
        ];
    }
}
