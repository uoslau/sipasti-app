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
        Schema::create('wilayah_tugas', function (Blueprint $table) {
            $table->id();
            $table->string('kode_wilayah')->unique();
            $table->string('nama_wilayah');
            $table->integer('honor_pendataan');
            $table->integer('honor_pengolahan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wilayah_tugas');
    }
};
