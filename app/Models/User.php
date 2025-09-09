<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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

    // Laravel s'attend à 'password', ici on redéfinit
    public function getAuthPassword()
    {
        return $this->motdepasse;
    }

    // Cast pour birthDate
    protected $casts = [
        'birthDate' => 'date:d-m-Y',
        'terms' => 'boolean',
    ];
}
