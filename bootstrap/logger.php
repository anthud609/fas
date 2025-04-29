<?php

declare(strict_types=1);

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\JsonFormatter;

class GlobalLogger
{
    private static ?Logger $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): Logger
    {
        if (self::$instance === null) {
            self::$instance = new Logger('app');
            
            // Retrieve environment variables
            $env = getenv('APP_ENV') ?: 'development';  // Default to 'production' if not set
            $debug = getenv('APP_DEBUG') === 'true';   // Parse the boolean value of APP_DEBUG
            
            // Pass them to the determineLogLevel method
            $logLevel = self::determineLogLevel($env, $debug);
            $logFile = __DIR__ . '/../logs/app.log';
            
            $handler = new StreamHandler($logFile, $logLevel);
            $handler->setFormatter(new JsonFormatter());
            
            self::$instance->pushHandler($handler);
            self::$instance->pushProcessor(function ($record) {
                // Add standard fields to every log entry
                $record['timestamp'] = (new DateTime())->format(DateTime::ATOM);
                $record['level'] = strtoupper($record['level_name']);
                $record['context'] = $record['context'] ?? [];
                
                // Add system information
                $record['context']['hostname'] = gethostname();
                $record['context']['app_env'] = getenv('APP_ENV') ?: 'production';
                $record['context']['app_debug'] = getenv('APP_DEBUG') === 'true';
                
                // Add request information if available
                if (PHP_SAPI !== 'cli') {
                    $record['context']['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
                    $record['context']['http_method'] = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
                    $record['context']['url'] = $_SERVER['REQUEST_URI'] ?? 'unknown';
                    $record['context']['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
                    $record['context']['referrer'] = $_SERVER['HTTP_REFERER'] ?? null;
                }
                
                // Add session/user information if available
                if (session_status() === PHP_SESSION_ACTIVE) {
                    $record['context']['user_id'] = $_SESSION['user_id'] ?? 'guest';
                    $record['context']['session_id'] = session_id();
                }
                
                // Reformat the record to match your desired structure
                $formattedRecord = [
                    'timestamp' => $record['timestamp'],
                    'level' => $record['level'],
                    'message' => $record['message'],
                    'context' => $record['context']
                ];
                
                // Add tags if present
                if (isset($record['context']['tags'])) {
                    $formattedRecord['tags'] = $record['context']['tags'];
                    unset($record['context']['tags']);
                }
                
                return $formattedRecord;
            });
        }
        
        return self::$instance;
    }
    

    private static function determineLogLevel(string $env, bool $debug): int
    {
        // In development or local environments with debugging enabled, log everything
    if ($env === 'development' && $debug) {
        return Logger::DEBUG;  // Log all levels (DEBUG, INFO, NOTICE, WARNING, ERROR, CRITICAL, ALERT, EMERGENCY)
    }

    // In staging or production, restrict the levels to higher severity
    if ($env === 'staging') {
        return $debug ? Logger::DEBUG : Logger::INFO;  // DEBUG and INFO for staging if debug is enabled, else INFO
    }

    // Default to ERROR for production to minimize log noise
    return Logger::ERROR;
    }

    public static function log(string $level, string $message, array $context = []): void
    {
        $logger = self::getInstance();
        $logger->log($level, $message, $context);
    }

    public static function __callStatic(string $method, array $args)
    {
        $validLevels = [
            'debug', 'info', 'notice', 'warning',
            'error', 'critical', 'alert', 'emergency'
        ];
        
        if (in_array(strtolower($method), $validLevels)) {
            $message = $args[0] ?? '';
            $context = $args[1] ?? [];
            return self::log($method, $message, $context);
        }
        
        return self::getInstance()->{$method}(...$args);
    }
}