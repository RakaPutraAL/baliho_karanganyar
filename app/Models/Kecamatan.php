<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kecamatan extends Model
{   protected $table = 'kecamatans';
    protected $fillable = [
        'kode',
        'nama_kecamatan',];

    // Relasi ke Baliho
    public function balihos()
    {
        return $this->hasMany(Baliho::class, 'kode', 'kode');
    }
}
