<?php

declare(strict_types=1);

namespace App\Logger;

use Throwable;

class FileLogger
{
    public static function logException(Throwable $e, string $context = 'error'): void
    {
        $logsDir = self::ensureLogsDirectory();

        $timestamp  = date('Y-m-d_H-i-s');
        $safeContext = preg_replace('/[^a-z0-9_]/', '_', strtolower($context));
        $filename   = "{$logsDir}/{$timestamp}_{$safeContext}.txt";

        $content = self::formatEntry($e);

        file_put_contents($filename, $content);
    }

    private static function formatEntry(Throwable $e): string
    {
        $lines = [
            '=== Exception Log ===',
            'Timestamp : ' . date('Y-m-d H:i:s'),
            'Class     : ' . $e::class,
            'Message   : ' . $e->getMessage(),
            'File      : ' . $e->getFile() . ':' . $e->getLine(),
            '',
            '--- Stack Trace ---',
            $e->getTraceAsString(),
        ];

        $previous = $e->getPrevious();
        while ($previous !== null) {
            $lines[] = '';
            $lines[] = '--- Caused by: ' . $previous::class . ' ---';
            $lines[] = 'Message : ' . $previous->getMessage();
            $lines[] = 'File    : ' . $previous->getFile() . ':' . $previous->getLine();
            $lines[] = $previous->getTraceAsString();
            $previous = $previous->getPrevious();
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    private static function ensureLogsDirectory(): string
    {
        $logsDir = dirname(__DIR__, 2) . '/logs';

        if (!is_dir($logsDir) && !mkdir($logsDir, 0755, true)) {
            return sys_get_temp_dir();
        }

        return $logsDir;
    }
}
