<?php

declare(strict_types=1);

// Autoload Composer dependencies
require __DIR__ . '/../vendor/autoload.php';

// Use GlobalLogger
use App\GlobalLogger;

// Log different levels of messages
GlobalLogger::debug('This is a debug message');
GlobalLogger::info('This is an info message');
GlobalLogger::notice('This is a notice message');
GlobalLogger::warning('This is a warning message');
GlobalLogger::error('This is an error message');
GlobalLogger::critical('This is a critical message');
GlobalLogger::alert('This is an alert message');
GlobalLogger::emergency('This is an emergency message');

// Confirming log levels via echo (optional, to check in the console)
echo 'Logging test completed!' . PHP_EOL;
