<?php

use App\Http\Controllers\KegiatanController;
use Illuminate\Support\Facades\Route;

Route::get('/kegiatan', [KegiatanController::class, 'index'])->name('kegiatan.index');
Route::post('/kegiatan', [KegiatanController::class, 'store'])->name('kegiatan.store');
Route::get('/kegiatan/{kegiatan}', [KegiatanController::class, 'show'])->name('kegiatan.show');
Route::get('/kegiatan/{kegiatan}/edit-kegiatan', [KegiatanController::class, 'edit'])->name('kegiatan.edit');
Route::put('/kegiatan/{kegiatan}', [KegiatanController::class, 'update'])->name('kegiatan.update');
Route::delete('/kegiatan/{kegiatan}', [KegiatanController::class, 'destroy'])->name('kegiatan.destroy');
