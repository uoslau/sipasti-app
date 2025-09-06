<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WilayahTugas extends Model
{
    /** @use HasFactory<\Database\Factories\WilayahTugasFactory> */
    use HasFactory;

    public function mitra()
    {
        return $this->hasMany(Mitra::class, 'wilayah_id', 'id');
    }
}
