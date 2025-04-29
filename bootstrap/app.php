<?php

declare(strict_types=1);

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Function to initialize the logger
function initLogger(): Logger {
    // Get environment variables for environment setup
    $env = getenv('APP_ENV') ?: 'production';
    $debug = getenv('APP_DEBUG') === 'true';

    // Create logger instance
    $logger = new Logger('global_logger');

    // Define the log file path (ensure this file is writable)
    $logFilePath = __DIR__ . '/../logs/app.log';
    $logLevel = determineLogLevelBasedOnEnv($env, $debug);

    // Add a StreamHandler to log messages to the file
    $handler = new StreamHandler($logFilePath, $logLevel);

    // Optional: Define custom log format
    $formatter = new LineFormatter(null, null, true, true);  // True for multi-line format
    $handler->setFormatter($formatter);
    $logger->pushHandler($handler);

    return $logger;
}

// Function to determine log level based on APP_ENV and APP_DEBUG
function determineLogLevelBasedOnEnv(string $env, bool $debug): int {
    if ($env === 'local') {
        // In local env, log all levels (DEBUG, INFO, NOTICE, etc.)
        return Logger::DEBUG;
    }

    if ($env === 'staging') {
        // In staging env, log DEBUG, INFO, WARNING, ERROR, CRITICAL
        return $debug ? Logger::DEBUG : Logger::INFO;
    }

    // In production, only log ERROR, CRITICAL, and ALERT
    return Logger::ERROR;
}

// Helper function to add context and log messages
function logWithContext(Logger $logger, string $level, string $message, array $context = []) {
    // Add contextual data to the log entry
    $context['timestamp'] = (new DateTime())->format(DateTime::ATOM);
    $context['level'] = $level;
    $context['hostname'] = gethostname();
    $context['app_env'] = getenv('APP_ENV');
    $context['app_debug'] = getenv('APP_DEBUG');
    $context['user_id'] = $_SESSION['user_id'] ?? 'guest';  // Assuming session-based auth

    // Additional relevant metadata (you can add more as needed)
    $context['request_id'] = $context['request_id'] ?? uniqid();  // Example: generate request ID
    $context['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'; // User-Agent
    $context['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';  // Client IP address
    $context['url'] = $_SERVER['REQUEST_URI'] ?? 'unknown';  // Current request URL

    // Log the message with the context
    $logger->log($level, $message, $context);
}

// Initialize logger
$logger = initLogger();

// Example usage of the logger: Log an INFO-level event with enriched data
// Example usage of the logger: Log an INFO-level event with enriched data
logWithContext($logger, 'INFO', 'User profile successfully updated', [
    'user_role' => 'admin',
    'execution_time_ms' => 125,
]);

