<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"prenom","nom","emailUniversitaire","motdepasse","terms"},
     *             @OA\Property(property="userType", type="string", example="student"),
     *             @OA\Property(property="prenom", type="string", example="John"),
     *             @OA\Property(property="nom", type="string", example="Doe"),
     *             @OA\Property(property="emailUniversitaire", type="string", example="john.doe@univ.com"),
     *             @OA\Property(property="motdepasse", type="string", format="password", example="secret123"),
     *             @OA\Property(property="terms", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Utilisateur créé"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'userType' => 'required|string',
            'prenom' => 'required|string',
            'nom' => 'required|string',
            'emailUniversitaire' => 'required|email|unique:users,emailUniversitaire',
            'motdepasse' => 'required|string|min:6',
            'terms' => 'required|boolean',
        ]);

        $user = User::create([
            'userType' => $data['userType'],
            'prenom' => $data['prenom'],
            'nom' => $data['nom'],
            'emailUniversitaire' => $data['emailUniversitaire'],
            'motdepasse' => Hash::make($data['motdepasse']),
            'terms' => $data['Terms'],
        ]);

        return response()->json($user, 201);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"emailUniversitaire","motdepasse"},
     *             @OA\Property(property="emailUniversitaire", type="string", example="john.doe@univ.com"),
     *             @OA\Property(property="motdepasse", type="string", format="password", example="secret123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Connexion réussie"),
     *     @OA\Response(response=401, description="Non autorisé")
     * )
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
                'emailUniversitaire' => ['Les informations d\'identification sont incorrectes.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['token' => $token], 200);
    }
}
