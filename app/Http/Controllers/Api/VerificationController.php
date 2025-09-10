<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\VerificationCodeMail;
use App\Models\VerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class VerificationController extends Controller
{
    /**
     * Génère, stocke et envoie un nouveau code de vérification.
     * Cette fonction est appelée par le frontend pour envoyer un code.
     */
    public function sendVerificationCode(Request $request)
    {
        try {
            $payload = $request->validate([
                'emailUniversitaire' => 'required'
            ]);
            $email = $payload['emailUniversitaire'];
            $code = str_pad(random_int(0, 99999), 5, '0', STR_PAD_LEFT);

            VerificationCode::updateOrCreate(
                ['emailUniversitaire' => $email],
                ['code' => $code, 'expires_at' => now()->addMinutes(15)]
            );

            Mail::to($email)->send(new VerificationCodeMail($code));

            return response()->json([
                'code' => 'success',
                'message' => 'Un nouveau code de vérification a été envoyé à votre adresse e-mail.',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['code' => 'error', 'message' => 'L\'adresse e-mail est invalide.'], 422);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 'error',
                'message' => 'Erreur lors de l\'envoi du code. Veuillez réessayer.',
            ], 500);
        }
    }

    /**
     * Valide le code de vérification fourni par le client.
     */
    public function verifyCode(Request $request)
    {
        try {
            $request->validate([
                'emailUniversitaire' => 'required|email',
                'code' => 'required|string|size:5',
            ]);

            $verification = VerificationCode::where('emailUniversitaire', $request->email)
                ->where('code', $request->code)
                ->first();

            if (!$verification) {
                return response()->json(['code' => 'error', 'message' => 'Code de vérification invalide.'], 400);
            }

            // Vérifie si le code a expiré
            if (now()->greaterThan($verification->expires_at)) {
                return response()->json(['code' => 'error', 'message' => 'Le code de vérification a expiré.'], 400);
            }

            // Le code est valide, on le supprime pour éviter une réutilisation
            $verification->delete();

            return response()->json(['code' => 'success', 'message' => 'Code vérifié avec succès.']);
        } catch (ValidationException $e) {
            return response()->json([
                'code' => 'error',
                'message' => 'Validation error.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 'error',
                'message' => 'Une erreur inattendue est survenue.',
            ], 500);
        }
    }
}
