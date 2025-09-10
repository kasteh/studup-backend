<?php

namespace App\Services\Orchestrators;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Exception;


class UserProspectOrchestrator
{
    /**
     * Crée ou met à jour un User
     * - CAS : on crée juste le User
     *
     * @param array $dataUser Données de l'utilisateur
     * @return array Infos de résultat (id_prospectzcrm, etc.)
     * @throws Exception
     */
    public function createOrUpdateUser(array $dataUser): array
    {
        $user = $this->createUserInDatabase($dataUser);

        return [
            'status'        => 'success',
            'userObject'    => $user,
            'iduser'        => $user->id
        ];
    }

    /**
     * Crée un User en base de données à partir de $dataUser.
     * Vous pouvez adapter les champs selon votre table User.
     *
     * @param array $dataUser
     * @return User
     */
    private function createUserInDatabase(array $dataUser): User
    {
        // Récupération des champs de base
        $userData = [
            'userType'             => $dataUser['userType'] ?? 'student',
            'prenom'               => $dataUser['prenom'] ?? null,
            'nom'                  => $dataUser['nom'] ?? null,
            'nationalite'          => $dataUser['nationalite'] ?? null,
            'birthDate'            => $dataUser['dateNaissance'] ?? null,
            'genre'                => $dataUser['genre'] ?? 'homme',
            'adresse'              => $dataUser['adresse'] ?? null,
            'codePostale'          => $dataUser['codePostale'] ?? null,
            'pays'                 => $dataUser['pays'] ?? null,
            'numeroTelephone'      => $dataUser['numeroTelephone'] ?? null,
            'emailUniversitaire'   => $dataUser['emailUniversitaire'] ?? null,
            'terms'                => $dataUser['terms'] ?? true,
        ];

        $userData['motdepasse'] = Hash::make($dataUser['motdepasse'] ?? 'defaultPwd');

        // Création de l'utilisateur en base
        return User::create($userData);
    }
}
