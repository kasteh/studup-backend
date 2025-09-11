<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Domaine;
use Illuminate\Http\JsonResponse;

class DomaineController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/domaines",
     *     summary="Liste des domaines d'études",
     *     tags={"Domaines"},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des domaines récupérée avec succès"
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        // Récupérer les domaines avec leurs disciplines associées
        $domaines = Domaine::with('disciplines')->get();

        return response()->json([
            'code'    => 'success',
            'message' => 'Liste des domaines récupérée avec succès',
            'data'    => $domaines,
        ], 200);
    }
}
