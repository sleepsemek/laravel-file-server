<?php

use App\Http\Controllers\API\FileController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\NoteController;
use App\Http\Controllers\API\PlantUMLController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/files', [FileController::class, 'index']);
    Route::post('/file', [FileController::class, 'store']);
    Route::get('/file/{filename}', [FileController::class, 'download']);
    Route::delete('/file/{filename}', [FileController::class, 'destroy']);
    Route::get('/file/{filename}/thumbnail', [FileController::class, 'thumbnail'])->name('api.file.thumbnail');

    Route::get('/notes', [NoteController::class, 'index']);
    Route::post('/note', [NoteController::class, 'store']);
    Route::patch('/note/{id}', [NoteController::class, 'update']);
    Route::delete('/note/{id}', [NoteController::class, 'destroy']);

    Route::match(['get', 'post'], '/uml', [PlantUMLController::class, 'index']);
});

Route::post('/login', [LoginController::class, 'login']);
