<?php

declare(strict_types=1);

namespace Service;

abstract class LogManager
{

    public static function addInfoLog(string $message): void
    {
        $file = fopen('../log/test.log', 'a+');
        fwrite($file, date('Y-m-d H:i:s') . ' [INFO] ' . $message . PHP_EOL);
        fclose($file);
    }

    public static function addErrorLog(string $message): void
    {
        $file = fopen('../log/test.log', 'a+');
        fwrite($file, date('Y-m-d H:i:s') . ' [ERROR] ' . $message . PHP_EOL);
        fclose($file);
    }

    public static function getLog(): array
    {
        $file = fopen('../log/test.log', 'r');
        $log = [];
        while ($line = fgets($file)) {
            $log[] = $line;
        }
        fclose($file);
        return $log;
    }

    public static function emptyLog(): void
    {
        $file = fopen('../log/test.log', 'w');
        fclose($file);
    }
}