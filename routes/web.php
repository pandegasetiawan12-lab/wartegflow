<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OcrController;

Route::get('/', function () {
    return view('welcome');
});

// Route API OCR WartegFlow
Route::post('/api/ocr', [OcrController::class, 'processOcr']);