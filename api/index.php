<?php

// Tampilkan semua error PHP murni jika terjadi masalah bootstrap
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('LARAVEL_START', microtime(true));

// Buat direktori temporary wajib Vercel
$storagePath = '/tmp/storage';
if (!file_exists($storagePath)) {
    @mkdir($storagePath . '/framework/views', 0755, true);
    @mkdir($storagePath . '/framework/sessions', 0755, true);
    @mkdir($storagePath . '/framework/cache/data', 0755, true);
    @mkdir($storagePath . '/logs', 0755, true);
}

try {
    require __DIR__ . '/../vendor/autoload.php';

    /** @var \Illuminate\Foundation\Application $app */
    $app = require_once __DIR__ . '/../bootstrap/app.php';

    // Set path storage ke /tmp
    $app->useStoragePath($storagePath);

    // Tangkap request & eksekusi Kernel
    $request = Illuminate\Http\Request::capture();
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle($request);

    $response->send();
    $kernel->terminate($request, $response);

} catch (\Throwable $e) {
    echo "<h1>Vercel PHP Execution Error</h1>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " on line " . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}