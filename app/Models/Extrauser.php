<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Extrauser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'niveauEtude',
        'domaineEtude',
        'disciplineEtude',
        'niveauEtudesouhaite',
        'document_url',
        'photo_url',
    ];

    protected $casts = [
        'domaineEtude' => 'array',
        'disciplineEtude' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
