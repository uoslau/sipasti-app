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
        Schema::create('mitras', function (Blueprint $table) {
            $table->id();
            $table->string('nik')->unique();
            $table->string('nama_mitra');
            $table->string('posisi');
            $table->string('email');
            $table->foreignId('wilayah_id')->constrained('wilayah_tugas')->onDelete('cascade');
            $table->string('alamat');
            $table->date('tanggal_lahir');
            $table->string('npwp')->nullable();
            $table->boolean('jenis_kelamin');
            $table->string('pekerjaan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mitras');
    }
};
