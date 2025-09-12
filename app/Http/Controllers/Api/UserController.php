<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/getUser",
     *     summary="Récupérer les informations de l'utilisateur connecté",
     *     tags={"Users"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Informations utilisateur récupérées avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="userType", type="string", example="student"),
     *                 @OA\Property(property="prenom", type="string", example="John"),
     *                 @OA\Property(property="nom", type="string", example="Doe"),
     *                 @OA\Property(
     *                     property="nationalite", 
     *                     type="array", 
     *                     @OA\Items(type="string"), 
     *                 ),
     *                 @OA\Property(property="birthDate", type="string", format="date", example="15-05-1995"),
     *                 @OA\Property(property="genre", type="string", example="male"),
     *                 @OA\Property(property="adresse", type="string", example="123 Rue Example"),
     *                 @OA\Property(property="codePostale", type="string", example="75001"),
     *                 @OA\Property(property="pays", type="string", example="France"),
     *                 @OA\Property(property="numeroTelephone", type="string", example="+33123456789"),
     *                 @OA\Property(property="emailUniversitaire", type="string", example="john.doe@universite.fr"),
     *                 @OA\Property(property="terms", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15 10:30:00"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15 10:30:00")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Utilisateur non authentifié")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erreur lors de la récupération des données utilisateur"),
     *             @OA\Property(property="error", type="string", example="Message d'erreur détaillé")
     *         )
     *     )
     * )
     */
    public function getUser(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }

            return response()->json([
                'success' => true,
                'data' => new UserResource($user)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des données utilisateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
