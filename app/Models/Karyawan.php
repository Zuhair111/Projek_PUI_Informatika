<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $table = 'karyawan';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'pengguna_id',
        'nip',
        'departemen',
        'no_hp',
        'jabatan',
        'foto_url',
    ];
}
