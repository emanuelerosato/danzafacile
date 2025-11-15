<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FcmToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    /**
     * Store or update FCM token
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string|max:255',
            'device_type' => 'required|in:android,ios,web',
            'device_id' => 'nullable|string|max:255',
        ]);

        $fcmToken = FcmToken::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'device_id' => $validated['device_id'] ?? null,
            ],
            [
                'token' => $validated['token'],
                'device_type' => $validated['device_type'],
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'data' => $fcmToken,
            'message' => 'Token FCM registrato con successo',
        ]);
    }

    /**
     * Delete FCM token (on logout)
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        FcmToken::where('user_id', $request->user()->id)
            ->where('token', $request->token)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Token FCM rimosso con successo',
        ]);
    }
}
