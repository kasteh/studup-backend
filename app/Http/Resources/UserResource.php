<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'userType' => $this->userType,
            'prenom' => $this->prenom,
            'nom' => $this->nom,
            'nationalite' => $this->nationalite,
            'birthDate' => $this->birthDate,
            'genre' => $this->genre,
            'adresse' => $this->adresse,
            'codePostale' => $this->codePostale,
            'pays' => $this->pays,
            'numeroTelephone' => $this->numeroTelephone,
            'emailUniversitaire' => $this->emailUniversitaire,
            'terms' => $this->terms,
        ];
    }
}
