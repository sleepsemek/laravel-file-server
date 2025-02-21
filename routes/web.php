<?php

use App\Http\Controllers\TorrentController;
use App\Http\Controllers\WebDavController;
use App\Models\File;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PlantUMLController;
use Illuminate\Support\Facades\URL;

URL::forceRootUrl('https://dev.teele.keenetic.pro');

Route::redirect('/', '/files');

Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.store');

Route::get('/file/shared/{token}', [FileController::class, 'shared']);

//Route::any('/webdav', [WebDavController::class, 'serve']);

Route::middleware('auth')->group(function () {
    Route::get('/files', [FileController::class, 'index'])->name('file.index');
    Route::post('/file', [FileController::class, 'store'])->name('file.store');
    Route::get('/file/{filename}/view', [FileController::class, 'view'])->name('file.view');
    Route::get('/file/{filename}/thumbnail', [FileController::class, 'thumbnail'])->name('file.thumbnail');
    Route::get('/file/{filename}', [FileController::class, 'download'])->name('file.download');
    Route::delete('/file/{filename}', [FileController::class, 'destroy'])->name('file.destroy');

    Route::get('/torrents', [TorrentController::class, 'index'])->name('torrent.index');
    Route::post('/torrent', [TorrentController::class, 'store'])->name('torrent.store');
    Route::get('/torrent/{hash}', [TorrentController::class, 'view'])->name('torrent.view');
    Route::post('/torrent/{hash}/stop', [TorrentController::class, 'stop'])->name('torrent.stop');
    Route::post('/torrent/{hash}/start', [TorrentController::class, 'start'])->name('torrent.start');
    Route::delete('/torrent/{hash}', [TorrentController::class, 'destroy'])->name('torrent.destroy');

    Route::get('/notes', [NoteController::class, 'index'])->name('note.index');
    Route::post('/note', [NoteController::class, 'store'])->name('note.store');
    Route::patch('/note/{id}', [NoteController::class, 'update'])->name('note.update');
    Route::delete('/note/{id}', [NoteController::class, 'destroy'])->name('note.destroy');
});

Route::match(['get', 'post'], '/uml', [PlantUMLController::class, 'index'])->name('uml.index');
Route::post( '/uml/svg', [PlantUMLController::class, 'getSvg'])->name('uml.svg');


