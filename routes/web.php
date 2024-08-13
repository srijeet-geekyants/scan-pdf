<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OcrController;

Route::get('/', function () {
    return redirect(route('ocr-files.index'));
});

Route::get('/ocr-files', [OcrController::class, 'index'])->name('ocr-files.index');
Route::post('/ocr-files/upload', [OcrController::class, 'upload'])->name('ocr-files.upload');
Route::get('/ocr-files/{ocrFileId}/openOcrPopup', [OcrController::class, 'openOcrPopup'])->name('ocr-files.openOcrPopup');
Route::post('/ocr-files/{ocrFileId}/saveCoordinates', [OcrController::class, 'saveCoordinates'])->name('ocr-files.saveCoordinates');
