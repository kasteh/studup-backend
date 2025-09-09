<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use OpenApi\Annotations as OA;

class TestController extends Controller
{
    /**
     * @OA\Get(
     *     path="/test",
     *     summary="Endpoint de test",
     *     @OA\Response(
     *         response=200,
     *         description="Réponse réussie"
     *     )
     * )
     */
    public function index()
    {
        return response()->json(['message' => 'Test OK']);
    }
}
