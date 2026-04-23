<?php

declare(strict_types=1);

namespace App\Logger;

use Throwable;

class FileLogger
{
    /**
     * Logs a throwable to a file in the logs/ directory at the project root.
     *
     * File name format: YYYY-MM-DD_HH-MM-SS_<context>.txt
     * Example: 2024-03-15_14-30-22_graphql_error.txt
     *
     * Each log entry includes:
     *   - Timestamp
     *   - Exception class
     *   - Message
     *   - File and line
     *   - Full stack trace
     */
    public static function logException(Throwable $e, string $context = 'error'): void
    {
        $logsDir = self::ensureLogsDirectory();

        $timestamp  = date('Y-m-d_H-i-s');
        $safeContext = preg_replace('/[^a-z0-9_]/', '_', strtolower($context));
        $filename   = "{$logsDir}/{$timestamp}_{$safeContext}.txt";

        $content = self::formatEntry($e);

        // FILE_APPEND is not used — each exception gets its own timestamped file
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

        // Include chained exceptions
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

    /**
     * Ensures the logs/ directory exists next to public/.
     * Returns the absolute path to the logs directory.
     */
    private static function ensureLogsDirectory(): string
    {
        // __DIR__ = src/Logger, go up two levels to project root
        $logsDir = dirname(__DIR__, 2) . '/logs';

        if (!is_dir($logsDir) && !mkdir($logsDir, 0755, true)) {
            // If we can't create the logs dir, fall back silently —
            // logging failure should not crash the application itself
            return sys_get_temp_dir();
        }

        return $logsDir;
    }
}
