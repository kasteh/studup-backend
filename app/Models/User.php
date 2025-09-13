<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'id';

    public $incrementig = true;

    protected $keyType = 'int';

    protected $fillable = [
        'userType',
        'prenom',
        'nom',
        'nationalite',
        'birthDate',
        'genre',
        'adresse',
        'codePostale',
        'pays',
        'numeroTelephone',
        'emailUniversitaire',
        'motdepasse',
        'terms',
    ];

    protected $hidden = [
        'motdepasse',
        'remember_token',
    ];

    protected $casts = [
        'birthDate' => 'date:d-m-Y',
        'terms' => 'boolean',
        'motdepsse' => 'hashed',
        'nationalite' => 'array',
    ];

    public function getAuthPassword()
    {
        return $this->motdepasse;
    }

    public function extrauser()
    {
        return $this->hasOne(Extrauser::class, 'user_id');
    }
}
