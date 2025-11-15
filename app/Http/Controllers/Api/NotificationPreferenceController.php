<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    /**
     * Get notification preferences for authenticated user
     */
    public function show(Request $request): JsonResponse
    {
        $preferences = NotificationPreference::firstOrCreate(
            ['user_id' => $request->user()->id],
            NotificationPreference::defaults()
        );

        return response()->json([
            'success' => true,
            'data' => $preferences,
        ]);
    }

    /**
     * Update notification preferences
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'enabled' => 'sometimes|boolean',
            'lesson_reminders' => 'sometimes|boolean',
            'reminder_minutes_before' => 'sometimes|integer|in:15,30,60,120,1440',
            'event_reminders' => 'sometimes|boolean',
            'payment_reminders' => 'sometimes|boolean',
            'system_notifications' => 'sometimes|boolean',
        ], [
            'reminder_minutes_before.in' => 'Il valore deve essere uno tra: 15, 30, 60, 120, 1440 minuti',
        ]);

        $preferences = NotificationPreference::updateOrCreate(
            ['user_id' => $request->user()->id],
            $validated
        );

        return response()->json([
            'success' => true,
            'data' => $preferences,
            'message' => 'Preferenze aggiornate con successo',
        ]);
    }
}
