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
        Schema::create('kegiatans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kegiatan');
            $table->boolean('is_ob')->default(false)->nullable();
            $table->string('slug')->unique();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('beban_anggaran');
            $table->foreignId('tim_kerja_id')->constrained('tim_kerjas')->onDelete('cascade');
            $table->integer('honor_nias')->nullable();
            $table->integer('honor_nias_barat')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kegiatans');
    }
};
