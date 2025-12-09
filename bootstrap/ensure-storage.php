<?php

/**
 * Bootstrap script to ensure all required storage directories exist
 * This script runs automatically and prevents "View path not found" errors
 * 
 * Run this script manually if needed: php bootstrap/ensure-storage.php
 */

$basePath = dirname(__DIR__);

// Required directories
$directories = [
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
    'storage/app/public',
    'resources/views',
];

$created = [];
$errors = [];

foreach ($directories as $dir) {
    $fullPath = $basePath . DIRECTORY_SEPARATOR . $dir;
    
    if (!is_dir($fullPath)) {
        if (mkdir($fullPath, 0755, true)) {
            $created[] = $dir;
        } else {
            $errors[] = "Failed to create: {$dir}";
        }
    }
    
    // Ensure directory is writable
    if (is_dir($fullPath) && !is_writable($fullPath)) {
        if (@chmod($fullPath, 0755)) {
            // Success
        } else {
            $errors[] = "Failed to make writable: {$dir}";
        }
    }
}

// Output results if run manually
if (php_sapi_name() === 'cli' && !empty($created)) {
    echo "Created directories:\n";
    foreach ($created as $dir) {
        echo "  - {$dir}\n";
    }
}

if (php_sapi_name() === 'cli' && !empty($errors)) {
    echo "Errors:\n";
    foreach ($errors as $error) {
        echo "  - {$error}\n";
    }
    exit(1);
}

