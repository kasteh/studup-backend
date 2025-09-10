<?php

namespace App\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\Orchestrators\UserProspectOrchestrator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Exception;

class AuthController extends Controller
{
    protected UserProspectOrchestrator $userProspectOrchestrator;

    public function __construct(UserProspectOrchestrator $userProspectOrchestrator)
    {
        $this->userProspectOrchestrator = $userProspectOrchestrator;
    }

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
        try {
            $payload = $request->validate([
                'data.data_user' => 'required|array',
            ]);

            $dataUser = $payload['data']['data_user'];

            $result = DB::transaction(function () use ($dataUser) {
                return $this->userProspectOrchestrator->createOrUpdateUser($dataUser);
            });

            $userEmail = $dataUser['emailUniversitaire'];

            $verificationRequest = new Request(['emailUniversitaire' => $userEmail]);
            $verificationController = new VerificationController();
            $verificationController->sendVerificationCode($verificationRequest);

            return response()->json([
                'code'      => 'success',
                'message'   => 'user_created',
                'iduser'    => $result['iduser'],
                'token'   => $result['token'] ?? null,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'code'    => 'error',
                'message' => 'validation_error',
                'errors'  => $e->errors(),
            ], 422);
        } catch (QueryException $qe) {
            // Gestion spécifique pour duplicate email
            if (isset($qe->errorInfo[1]) && $qe->errorInfo[1] == 23505) {
                return response()->json([
                    'code'    => 'error',
                    'message' => 'email_already_exists'
                ], 422);
            }
            return response()->json([
                'code'         => 'error',
                'message'      => 'sql_problem',
                'errorDetails' => config('app.env') !== 'production'
                    ? $qe->getMessage()
                    : null
            ], 500);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            return response()->json([
                'code'         => 'error',
                'message'      => $errorMessage,
                'errorDetails' => config('app.env') !== 'production'
                    ? $e->getMessage()
                    : null
            ], 500);
        }
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
        $payload = $request->validate([
            'emailUniversitaire' => 'required|email',
            'motdepasse' => 'required|string',
        ]);

        $email = $payload['emailUniversitaire'];
        $motdepasse = $payload['motdepasse'];

        $user = User::where('emailUniversitaire', $email)->first();

        if (! $user || ! Hash::check($motdepasse, $user->motdepasse)) {
            throw ValidationException::withMessages([
                'emailUniversitaire' => ['Les informations d\'identification sont incorrectes.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'code'      => 'success',
            'message'   => 'successfully Login',
            'token' => $token,
            'userType'  => $user->userType,
        ], 200);
    }
}
