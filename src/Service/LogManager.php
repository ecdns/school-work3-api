<?php

declare(strict_types=1);

namespace Service;

abstract class LogManager
{

    public static function getContext(): string
    {
        $context = '';
        if (isset($_SERVER['REQUEST_URI'])) {
            $context = $_SERVER['REQUEST_URI'];
        }

        if (isset($_SERVER['HTTP_METHOD'])) {
            $context .= ' - ' . $_SERVER['HTTP_METHOD'];
        }

        if (isset($_SERVER['HTTP_REFERER'])) {
            $context .= ' - ' . $_SERVER['HTTP_REFERER'];
        }

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $context .= ' - ' . $_SERVER['HTTP_USER_AGENT'];
        }

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $context .= ' - ' . $_SERVER['REMOTE_ADDR'];
        }

        return $context;
    }

    public static function addInfoLog(string $message): void
    {
        $file = fopen('log/info.log', 'a+');
        fwrite($file, date('Y-m-d H:i:s') . ' [INFO] ' . $message . PHP_EOL);
        fclose($file);
    }

    public static function addErrorLog(string $message): void
    {
        $file = fopen('log/error.log', 'a+');
        fwrite($file, date('Y-m-d H:i:s') . ' [ERROR] ' . $message . PHP_EOL);
        fclose($file);
    }

    public static function getLog($type): array
    {
        $file = fopen('log/' . $type . '.log', 'r'); // 'r
        $log = [];
        while ($line = fgets($file)) {
            $log[] = $line;
        }
        fclose($file);
        return $log;
    }

    public static function emptyLog($type): void
    {
        $file = fopen('log/' . $type . '.log', 'w');
        fclose($file);
    }
}