<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Domaine extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom'
    ];

    public function disciplines()
    {
        return $this->hasMany(Discipline::class);
    }
}
