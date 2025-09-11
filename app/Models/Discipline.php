<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Discipline extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'domaine_id'
    ];

    public function domaine()
    {
        return $this->belongsTo(Domaine::class);
    }
}
