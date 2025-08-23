<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('nomor_kontraks', function (Blueprint $table) {
            $table->id();
            $table->string('nik');
            $table->foreignId('kegiatan_id')->constrained('kegiatans')->onDelete('cascade');
            $table->integer('tahun');
            $table->integer('bulan');
            $table->integer('nomor_kontrak');
            $table->integer('nomor_bast');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nomor_kontraks');
    }
};
