<?php

declare(strict_types=1);

use Monolog\Logger;
use Throwable;

class ErrorHandler
{
    /**
     * Initialize the custom error handler for both errors and exceptions.
     */
    public static function init(): void
    {
        // Set custom error handler for PHP errors (fatal, warnings, notices, etc.)
        set_error_handler([self::class, 'handleError']);

        // Set custom exception handler
        set_exception_handler([self::class, 'handleException']);

        // Set shutdown function to catch fatal errors
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /**
     * Handle PHP errors and map them to Monolog levels.
     */
    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        // Filter out certain errors that you want to suppress
        if (!(error_reporting() & $severity)) {
            return false;
        }

        // Map PHP error levels to Monolog levels
        $level = self::mapErrorToLogLevel($severity);

        // Prepare log context with error details
        $context = [
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'error_code' => $severity,
            'stack_trace' => null,
            'tags' => ['error', 'php']
        ];

        // Log the error message with the appropriate Monolog level
        GlobalLogger::$level($message, $context);

        // Don't stop PHP's internal error handling
        return true;
    }

    /**
     * Map PHP error types to Monolog levels.
     */
    private static function mapErrorToLogLevel(int $severity): string
    {
        switch ($severity) {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                return 'critical'; // Critical errors
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
                return 'warning';  // Warnings
            case E_NOTICE:
            case E_USER_NOTICE:
                return 'info';     // Notices (typically non-critical)
            default:
                return 'error';    // Default for unknown error levels
        }
    }

    /**
     * Handle uncaught exceptions.
     */
    public static function handleException(Throwable $exception): void
    {
        // Prepare log context with exception details
        $context = [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'stack_trace' => $exception->getTraceAsString(),
            'tags' => ['error', 'exception']
        ];

        // Log the exception message
        GlobalLogger::error($exception->getMessage(), $context);
    }

    /**
     * Handle fatal errors that PHP can't catch with a normal error handler.
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error !== null) {
            // Prepare log context for the fatal error
            $context = [
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
                'error_code' => $error['type'],
                'stack_trace' => null,
                'tags' => ['error', 'fatal']
            ];

            // Log the fatal error
            GlobalLogger::critical($error['message'], $context);
        }
    }
}
