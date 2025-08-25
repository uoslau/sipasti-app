<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class PetugasKegiatan extends Model
{
    /** @use HasFactory<\Database\Factories\PetugasKegiatanFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function mitra()
    {
        return $this->belongsTo(Mitra::class, 'nik', 'nik');
    }

    public function wilayahTugas()
    {
        return $this->belongsTo(WilayahTugas::class, 'kode_wilayah');
    }

    public function nomorKontrak()
    {
        return $this->hasOne(NomorKontrak::class, 'nik', 'nik')
            ->where('kegiatan_id', $this->kegiatan_id);
    }

    public function timKerja(): HasOneThrough
    {
        return $this->hasOneThrough(
            TimKerja::class,
            Kegiatan::class,
            'id', // Foreign key di tabel Kegiatan
            'id', // Foreign key di tabel TimKerja
            'kegiatan_id', // Local key di tabel PetugasKegiatan
            'tim_kerja_id' // Local key di tabel Kegiatan
        );
    }

    public function getRouteKeyName()
    {
        return 'nik';
    }

    protected static function booted()
    {
        static::deleting(function ($petugasKegiatan) {
            $petugasKegiatan->nomorKontrak()->delete();
        });
    }
}
