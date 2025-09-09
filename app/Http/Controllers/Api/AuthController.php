<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $request->validate([
            'userType' => 'required|in:student,organism,admin',
            'prenom' => 'required|string|max:255',
            'nom' => 'required|string|max:255',
            'nationalite' => 'nullable|string|max:255',
            'birthDate' => 'required|date_format:d-m-Y',
            'genre' => 'nullable|in:male,female,other',
            'adresse' => 'nullable|string|max:255',
            'codePostale' => 'nullable|string|max:20',
            'pays' => 'nullable|string|max:255',
            'numeroTelephone' => 'nullable|string|max:20',
            'emailUniversitaire' => 'required|email|unique:users,emailUniversitaire',
            'motdepasse' => 'required|string|min:6|confirmed',
            'terms' => 'accepted',
        ]);

        $user = User::create([
            'userType' => $request->userType,
            'prenom' => $request->prenom,
            'nom' => $request->nom,
            'nationalite' => $request->nationalite,
            'birthDate' => $request->birthDate,
            'genre' => $request->genre,
            'adresse' => $request->adresse,
            'codePostale' => $request->codePostale,
            'pays' => $request->pays,
            'numeroTelephone' => $request->numeroTelephone,
            'emailUniversitaire' => $request->emailUniversitaire,
            'motdepasse' => Hash::make($request->motdepasse),
            'terms' => $request->terms,
        ]);

        // Crée un token pour l'utilisateur
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $request->validate([
            'emailUniversitaire' => 'required|email',
            'motdepasse' => 'required|string',
        ]);

        $user = User::where('emailUniversitaire', $request->emailUniversitaire)->first();

        if (! $user || ! Hash::check($request->motdepasse, $user->motdepasse)) {
            throw ValidationException::withMessages([
                'emailUniversitaire' => ['Les identifiants sont incorrects.'],
            ]);
        }

        // Supprime les anciens tokens
        $user->tokens()->delete();

        // Crée un nouveau token
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    /**
     * Logout user (revoke current token)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie'
        ]);
    }
}
