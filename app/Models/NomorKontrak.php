<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NomorKontrak extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function petugasKegiatan()
    {
        return $this->belongsTo(PetugasKegiatan::class, 'nik', 'nik');
    }

    public function getFullNomorKontrakAttribute()
    {
        return str_pad($this->nomor_kontrak, 3, '0', STR_PAD_LEFT) . "/1201_MITRA/" . $this->tahun;
    }

    public function getFullNomorBastAttribute()
    {
        return str_pad($this->nomor_bast, 3, '0', STR_PAD_LEFT) . "/1201_BAST/" . $this->tahun;
    }
}
