<?php

// Arahkan folder penulisan storage dan cache ke /tmp serverless Vercel
$app = require __DIR__ . '/../bootstrap/app.php';

$app->useStoragePath('/tmp/storage');

if (!file_exists('/tmp/storage')) {
    mkdir('/tmp/storage/framework/views', 0755, true);
    mkdir('/tmp/storage/framework/sessions', 0755, true);
    mkdir('/tmp/storage/framework/cache', 0755, true);
    mkdir('/tmp/storage/logs', 0755, true);
}

// Jalankan aplikasi
$request = Illuminate\Http\Request::capture();
$response = $app->handle($request);
$response->send();