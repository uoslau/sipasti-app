<?php

namespace App\Models;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kegiatan extends Model
{
    use HasSlug, HasFactory, SoftDeletes;

    protected $guarded = ['id', 'slug'];

    protected $casts = [
        'is_generated' => 'boolean',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('nama_kegiatan')
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function timKerja()
    {
        return $this->belongsTo(TimKerja::class, 'tim_kerja_id');
    }

    public function petugasKegiatan()
    {
        return $this->hasMany(PetugasKegiatan::class, 'kegiatan_id', 'id')->whereNull('deleted_at');
    }

    protected static function booted()
    {
        static::deleting(function ($kegiatan) {
            foreach ($kegiatan->petugasKegiatan as $petugas) {
                $petugas->delete();
            }
        });
    }
}
