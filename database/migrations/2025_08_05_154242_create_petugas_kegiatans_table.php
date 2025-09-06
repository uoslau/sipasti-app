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
        Schema::create('petugas_kegiatans', function (Blueprint $table) {
            $table->id();
            $table->string('nik');
            $table->foreignId('kegiatan_id')->constrained('kegiatans')->onDelete('cascade');
            $table->string('bertugas_sebagai');
            $table->integer('beban_kerja')->default(0);
            $table->string('satuan_beban_kerja');
            $table->integer('honor');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petugas_kegiatans');
    }
};
