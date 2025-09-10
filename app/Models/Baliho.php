<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Baliho extends Model
{
    protected $fillable = [
        'opd_id',
        'jenis_baliho',
        'pemasangan',
        'view',
        'dimensi',
        'jenis_kontruksi',
        'alamat',
        'kode', 
        'latitude',
        'longitude',
        'foto',
    ];

    
    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'kode', 'kode'); 
    }

    public function opd()
{
    return $this->belongsTo(Opd::class, 'opd_id', 'id');
}

}
