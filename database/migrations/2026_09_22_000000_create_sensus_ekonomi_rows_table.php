<?php

/*
 * SEMENTARA — fitur Sensus Ekonomi.
 *
 * Fitur ini berdiri sendiri dan tidak berhubungan dengan modul SIPASTI lain.
 * Untuk menghapusnya: hapus file migrasi ini, model SensusEkonomiRow,
 * App\Imports\SensusEkonomiImport, SensusEkonomiController,
 * folder resources/views/sensus-ekonomi, public/css & public/js sensus-ekonomi,
 * lalu hapus blok route "sensus-ekonomi" dan entri menunya di sidebar.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensus_ekonomi_rows', function (Blueprint $table) {
            $table->id();
            $table->string('email_pencacah')->nullable();
            $table->string('level_2_full_code')->nullable();
            $table->string('nama_kecamatan')->nullable();
            $table->string('level_4_name')->nullable();
            $table->string('level_5_name')->nullable();
            $table->string('nama_kk')->nullable();
            $table->string('nama_dtsen')->nullable();
            $table->string('nik_dtsen', 32)->nullable();
            $table->string('ada_keluarga_label')->nullable();
            $table->text('catatan')->nullable();
            $table->text('link_fasih')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensus_ekonomi_rows');
    }
};
