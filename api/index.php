<?php

// Tampilkan error jika ada masalah internal
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('LARAVEL_START', microtime(true));

// Siapkan folder storage temporary di Vercel
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
    if (isset($_GET['diag'])) {
    header('Content-Type: text/plain');
    echo "ViewServiceProvider ada: " . (class_exists(\Illuminate\View\ViewServiceProvider::class) ? 'ya' : 'tidak') . "\n";
    echo "View terikat sebelum boot: " . ($app->bound('view') ? 'ya' : 'tidak') . "\n";
    echo "Services cache: " . $app->getCachedServicesPath() . " | ada: " . (file_exists($app->getCachedServicesPath()) ? 'ya' : 'tidak') . "\n";
    echo "Packages cache: " . $app->getCachedPackagesPath() . " | ada: " . (file_exists($app->getCachedPackagesPath()) ? 'ya' : 'tidak') . "\n";
    $app->boot();
    echo "View terikat setelah boot: " . ($app->bound('view') ? 'ya' : 'tidak') . "\n";
    echo "Provider yang dimuat:\n" . implode("\n", array_keys($app->getLoadedProviders())) . "\n";
    echo "Cache services ada: " . (file_exists('/tmp/storage/bootstrap/cache/services.php') ? 'ya' : 'tidak') . "\n";
    exit;
}

    // Arahkan storage path ke /tmp
    $app->useStoragePath($storagePath);

    // Tangkap request
    $request = Illuminate\Http\Request::capture();

    // Biarkan Kernel menangani boot & eksekusi secara alami
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