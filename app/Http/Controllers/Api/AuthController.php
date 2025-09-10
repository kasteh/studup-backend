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
     *             required={"prenom","nom","emailUniversitaire","motdepasse","terms","userType","nationalite","birthDate","genre","adresse","codePostale","pays","numeroTelephone"},
     *             @OA\Property(property="userType", type="string", example="student"),
     *             @OA\Property(property="prenom", type="string", example="John"),
     *             @OA\Property(property="nom", type="string", example="Doe"),
     *             @OA\Property(property="nationalite", type="string", example="Française"),
     *             @OA\Property(property="birthDate", type="string", format="date", example="2000-05-15"),
     *             @OA\Property(property="genre", type="string", example="male"),
     *             @OA\Property(property="adresse", type="string", example="123 Rue de Paris"),
     *             @OA\Property(property="codePostale", type="string", example="75001"),
     *             @OA\Property(property="pays", type="string", example="France"),
     *             @OA\Property(property="numeroTelephone", type="string", example="+33123456789"),
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
            'userType' => 'required|in:student,organism,admin',
            'prenom' => 'required|string|max:255',
            'nom' => 'required|string|max:255',
            'nationalite' => 'required|string|max:255',
            'birthDate' => 'required|date',
            'genre' => 'required|in:male,female,other',
            'adresse' => 'required|string|max:255',
            'codePostale' => 'required|string|max:20',
            'pays' => 'required|string|max:255',
            'numeroTelephone' => 'required|string|max:20',
            'emailUniversitaire' => 'required|email|unique:users,emailUniversitaire',
            'motdepasse' => 'required|string|min:6',
            'terms' => 'required|boolean',
        ]);

        $user = User::create([
            'userType' => $data['userType'],
            'prenom' => $data['prenom'],
            'nom' => $data['nom'],
            'nationalite' => $data['nationalite'],
            'birthDate' => $data['birthDate'],
            'genre' => $data['genre'],
            'adresse' => $data['adresse'],
            'codePostale' => $data['codePostale'],
            'pays' => $data['pays'],
            'numeroTelephone' => $data['numeroTelephone'],
            'emailUniversitaire' => $data['emailUniversitaire'],
            'motdepasse' => Hash::make($data['motdepasse']),
            'terms' => $data['terms'],
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
