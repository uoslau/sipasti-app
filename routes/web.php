<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\PetugasKegiatanController;

Route::get('/kegiatan', [KegiatanController::class, 'index'])
    ->name('kegiatan.index');
Route::post('/kegiatan', [KegiatanController::class, 'store'])
    ->name('kegiatan.store');
Route::get('/kegiatan/{kegiatan}/edit-kegiatan', [KegiatanController::class, 'edit'])
    ->name('kegiatan.edit');
Route::put('/kegiatan/{kegiatan}', [KegiatanController::class, 'update'])
    ->name('kegiatan.update');
Route::delete('/kegiatan/{kegiatan}', [KegiatanController::class, 'destroy'])
    ->name('kegiatan.destroy');


Route::delete('/kegiatan/{kegiatan}/{petugasKegiatan}', [PetugasKegiatanController::class, 'destroy'])
    ->name('petugas.destroy');
Route::get('search-mitra', [PetugasKegiatanController::class, 'search'])
    ->name('petugas.search');
Route::post('/kegiatan/{kegiatan}/edit-kegiatan', [PetugasKegiatanController::class, 'store'])
    ->name('petugas.store');
Route::get('/kegiatan/{kegiatan}/{petugasKegiatan}/edit-petugas', [PetugasKegiatanController::class, 'edit'])
    ->name('petugas.edit');
Route::put('/kegiatan/{kegiatan}/{petugasKegiatan}/edit-petugas', [PetugasKegiatanController::class, 'update'])
    ->name('petugas.update');
Route::post('/kegiatan/{kegiatan}/petugas-import', [PetugasKegiatanController::class, 'import'])
    ->name('petugas.import');
Route::post('/kegiatan/{kegiatan}/petugas-import-update', [PetugasKegiatanController::class, 'import_update'])
    ->name('petugas.import_update');

Route::get('/download/{nama_file}', function ($nama_file) {
    $file_path = storage_path("app/public/template/{$nama_file}");
    if (!file_exists($file_path)) {
        abort(404, 'File tidak ditemukan');
    }
    return Response::download($file_path, $nama_file);
})->name('file.download');
