<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Opd extends Model
{
    use HasFactory;
 protected $table = 'opds';
    protected $fillable = [
        'nama_opd', 
    ];

    public function balihos()
    {
        return $this->hasMany(Baliho::class, 'opd_id');
    }
}
