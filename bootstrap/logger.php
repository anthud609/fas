<?php

namespace App;

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
            $env = getenv('APP_ENV') ?: 'development';
            $debug = getenv('APP_DEBUG') === 'true';
            $logLevel = self::determineLogLevel($env, $debug);
            $logFile = __DIR__ . '/../logs/app.log';
            $handler = new StreamHandler($logFile, $logLevel);
            $handler->setFormatter(new JsonFormatter());
            self::$instance->pushHandler($handler);
        }
        return self::$instance;
    }

    private static function determineLogLevel(string $env, bool $debug): int
    {
        if ($env === 'development' && $debug) {
            return Logger::DEBUG;
        }
        return Logger::ERROR;
    }

    public static function log(string $level, string $message, array $context = []): void
    {
        $logger = self::getInstance();
        $logger->log($level, $message, $context);
    }

    public static function __callStatic(string $method, array $args)
    {
        $validLevels = ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'];
        if (in_array(strtolower($method), $validLevels)) {
            $message = $args[0] ?? '';
            $context = $args[1] ?? [];
            return self::log($method, $message, $context);
        }
    }
}
