<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\KontrakController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MitraController;
use App\Http\Controllers\NomorKontrakController;
use App\Http\Controllers\PetugasKegiatanController;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'index'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'authenticate']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'prevent-back-history'])->group(function () {
    // sementara di comment dulu sebelum fix dashboard & diarahin ke kegiatan index
    // Route::get('/', [DashboardController::class, 'index'])
    //     ->name('dashboard.index');
    Route::get('/', [KegiatanController::class, 'index'])
        ->name('dashboard.index');
    // Route::get('/api/kegiatan-chart', [DashboardController::class, 'getChartData']);

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

    Route::post('/kegiatan/{kegiatan}/edit-kegiatan/generate', [NomorKontrakController::class, 'generate'])
        ->name('kontrak.generate');

    Route::get('/kegiatan/download/{kegiatan}', [DownloadController::class, 'downloadBAST'])
        ->name('kegiatan.download')
        ->withoutMiddleware('prevent-back-history');
    Route::get('/kontrak/{slug}', [DownloadController::class, 'downloadSPK'])
        ->name('kontrak.download')
        ->withoutMiddleware('prevent-back-history');
    Route::post('/kegiatan/download/{kegiatan}/ob', [DownloadController::class, 'uploadOB'])
        ->name('kegiatanob.upload')
        ->withoutMiddleware('prevent-back-history');
    Route::get('/kegiatan/download/{kegiatan}/ob', [DownloadController::class, 'downloadOB'])
        ->name('kegiatanob.download')
        ->withoutMiddleware('prevent-back-history');

    Route::get('/download/{nama_file}', function ($nama_file) {
        $file_path = storage_path("app/public/template/{$nama_file}");
        if (!file_exists($file_path)) {
            abort(404, 'File tidak ditemukan');
        }
        return Response::download($file_path, $nama_file);
    })->name('file.download')
        ->withoutMiddleware('prevent-back-history');

    Route::get('/kontrak', [KontrakController::class, 'index'])
        ->name('kontrak.index');

    Route::get('/mitra', [MitraController::class, 'index'])
        ->name('mitra.index');

    Route::get('/mitra/rekap-kegiatan/export', [MitraController::class, 'exportExcel'])->name('mitra.export');
});
