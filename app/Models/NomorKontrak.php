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
}
