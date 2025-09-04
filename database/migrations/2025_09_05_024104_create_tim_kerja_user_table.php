<?php

use App\Models\User;
use App\Models\TimKerja;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tim_kerja_user', function (Blueprint $table) {
            $table->foreignIdFor(User::class)->constrained()->onDelete('cascade');

            // Kolom ini akan menghubungkan ke tabel 'tim_kerjas'
            $table->foreignIdFor(TimKerja::class)->constrained()->onDelete('cascade');

            // Menetapkan primary key gabungan agar tidak ada user yang sama di tim yang sama
            $table->primary(['user_id', 'tim_kerja_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tim_kerja_user');
    }
};
