<?php

/*
 * SEMENTARA — fitur Sensus Ekonomi.
 * Model mandiri, tidak berelasi dengan model SIPASTI mana pun.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensusEkonomiRow extends Model
{
    protected $table = 'sensus_ekonomi_rows';

    protected $guarded = ['id'];
}
