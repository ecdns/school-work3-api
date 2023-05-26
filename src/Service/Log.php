<?php

declare(strict_types=1);

namespace Service;

class Log
{

    public function getContext(): string
    {
        $context = '';

        if (isset($_SERVER['REQUEST_URI'])) {
            $context = $_SERVER['REQUEST_URI'];
        }

        if (isset($_SERVER['REQUEST_METHOD'])) {
            $context .= ' - ' . $_SERVER['REQUEST_METHOD'];
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

    // Unused for now
    public function getFullContext(): string
    {

        $context = $this->getContext();

        // get the whole backtrace and add it to the context
        $backtrace = debug_backtrace();

        foreach ($backtrace as $trace) {
            if (isset($trace['file'])) {
                $context .= ' - ' . $trace['file'];
            }
            if (isset($trace['line'])) {
                $context .= ' on line ' . $trace['line'];
            }
        }

        return $context;
    }

    public function addSuccessLog(string $message): void
    {
        $file = fopen('../log/success.log', 'a+');
        fwrite($file, date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL);
        fclose($file);
    }

    public function addErrorLog(string $message): void
    {
        $file = fopen('../log/error.log', 'a+');
        fwrite($file, date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL);
        fclose($file);
    }

    public function getLog($type): array
    {
        $file = fopen('log/' . $type . '.log', 'r'); // 'r
        $log = [];
        while ($line = fgets($file)) {
            $log[] = $line;
        }
        fclose($file);
        return $log;
    }

    public function emptyLog($type): void
    {
        $file = fopen('log/' . $type . '.log', 'w');
        fclose($file);
    }
}