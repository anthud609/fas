<?php
declare(strict_types=1);

// 1) Composer autoload – must come first
require __DIR__ . '/../vendor/autoload.php';

// 2) Bootstrap your logger (loads Dotenv + logger.php + init())
require __DIR__ . '/../bootstrap/app.php';

// 3) Now GlobalLogger exists—fire a test log

// Test all log levels with varying message types and context

// DEBUG Level (Developer logs)
logWithContext($logger, 'DEBUG', 'This is a debug log for tracing the flow.', [
    'user_id' => '123',
    'context' => 'Debugging flow',
    'execution_time_ms' => 50,
]);

// INFO Level (Informational logs)
logWithContext($logger, 'INFO', 'User logged in successfully.', [
    'user_id' => '123',
    'user_role' => 'admin',
    'execution_time_ms' => 150,
]);

// NOTICE Level (Non-critical notices)
logWithContext($logger, 'NOTICE', 'User profile has an incomplete field.', [
    'user_id' => '123',
    'profile_field' => 'address',
]);

// WARNING Level (Issues that could affect functionality)
logWithContext($logger, 'WARNING', 'Password attempt limit nearing.', [
    'user_id' => '123',
    'attempts_left' => 2,
]);

// ERROR Level (Critical errors)
try {
    nonExistentFunction();  // This will trigger a PHP warning and exception
} catch (Throwable $e) {
    logWithContext($logger, 'ERROR', 'Caught an exception or error: ' . $e->getMessage(), [
        'exception' => $e,
        'user_id' => '123',
    ]);
}

// CRITICAL Level (Serious errors that terminate the script)
logWithContext($logger, 'CRITICAL', 'Database connection lost.', [
    'host' => 'localhost',
    'db_status' => 'disconnected',
]);

// ALERT Level (Immediate attention needed)
logWithContext($logger, 'ALERT', 'System memory usage is over the limit!', [
    'cpu_usage' => '95%',
    'memory_usage' => '95%',
]);

// EMERGENCY Level (System or application failure)
logWithContext($logger, 'EMERGENCY', 'Server crashed! Immediate action required.', [
    'server_id' => 'server01',
    'reason' => 'hardware failure',
]);

// 4) Confirm in the browser
echo 'Logger test complete.';
