<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Tentukan lokasi storage di folder temporary Vercel
$storagePath = '/tmp/storage';

if (!file_exists($storagePath)) {
    mkdir($storagePath . '/framework/views', 0755, true);
    mkdir($storagePath . '/framework/sessions', 0755, true);
    mkdir($storagePath . '/framework/cache', 0755, true);
    mkdir($storagePath . '/logs', 0755, true);
}

// Muat Autoload & Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Timpa storage path bawaan ke /tmp/storage
$app->useStoragePath($storagePath);

// Handle Request
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);