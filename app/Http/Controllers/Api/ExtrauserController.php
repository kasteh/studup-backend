<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Extrauser;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ExtrauserController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'niveauEtude' => 'nullable|string|max:255',
            'domaineEtude' => 'nullable|array',
            'disciplineEtude' => 'nullable|array',
            'niveauEtudesouhaite' => 'nullable|string|max:255',
            'document' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'photo' => 'nullable|image|max:5120',
            'pays' => 'required|array',
            'period' => 'required|string',
            'langue' => 'required|array',
            'niveauLangue' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // Upload document
        $documentUrl = null;
        if ($request->hasFile('document')) {
            $documentUrl = $request->file('document')->store('documents', 'public');
        }

        // Upload photo
        $photoUrl = null;
        if ($request->hasFile('photo')) {
            $photoUrl = $request->file('photo')->store('photos', 'public');
        }

        $extrauser = Extrauser::updateOrCreate(
            ['user_id' => $data['user_id']],
            [
                'niveauEtude' => $data['niveauEtude'] ?? null,
                'domaineEtude' => $data['domaineEtude'] ?? null,
                'disciplineEtude' => $data['disciplineEtude'] ?? null,
                'niveauEtudesouhaite' => $data['niveauEtudesouhaite'] ?? null,
                'pays' => $data['pays'] ?? null,
                'period' => $data['period'] ?? null,
                'langue' => $data['langue'] ?? null,
                'niveauLangue' => $data['niveauLangue'] ?? null,
                'document_url' => $documentUrl ? Storage::url($documentUrl) : null,
                'photo_url' => $photoUrl ? Storage::url($photoUrl) : null,
            ]
        );

        return response()->json([
            'code'      => 'success',
            'message'   => 'Informations enregistrées avec succès.',
            'data'      => $extrauser
        ]);
    }
}
