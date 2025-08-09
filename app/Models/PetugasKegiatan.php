<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PetugasKegiatan extends Model
{
    /** @use HasFactory<\Database\Factories\PetugasKegiatanFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function Kegiatan()
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function Mitra()
    {
        return $this->belongsTo(Mitra::class, 'nik', 'nik');
    }

    public function getRouteKeyName()
    {
        return 'nik';
    }
}
