<?php

namespace App\Services;

use App\Models\FcmToken;
use App\Models\NotificationLog;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Exception\MessagingException;
use Illuminate\Support\Facades\Log;

class FirebasePushService
{
    protected $messaging;

    public function __construct()
    {
        try {
            // Get credentials path from config (NOT env - works with config cache)
            $credentialsPath = config('firebase.credentials.file');

            if (!$credentialsPath) {
                throw new \Exception('Firebase credentials path not set in config/firebase.php');
            }

            // If path is relative, make it absolute from base_path
            if (!str_starts_with($credentialsPath, '/')) {
                $credentialsPath = base_path($credentialsPath);
            }

            if (!file_exists($credentialsPath)) {
                throw new \Exception("Firebase credentials file not found: {$credentialsPath}");
            }

            $factory = (new Factory)->withServiceAccount($credentialsPath);
            $this->messaging = $factory->createMessaging();
        } catch (\Exception $e) {
            Log::error('Firebase initialization failed: ' . $e->getMessage());
            $this->messaging = null;
        }
    }

    /**
     * Invia notifica push a singolo utente
     *
     * @param int $userId
     * @param string $title
     * @param string $body
     * @param array $data Additional payload data
     * @param int|null $lessonId
     * @return array ['success' => bool, 'sent_count' => int, 'failed_tokens' => array]
     */
    public function sendToUser(int $userId, string $title, string $body, array $data = [], ?int $lessonId = null): array
    {
        if (!$this->messaging) {
            Log::error('Firebase messaging not initialized');
            return ['success' => false, 'sent_count' => 0, 'failed_tokens' => []];
        }

        // Get all active tokens for this user
        $tokens = FcmToken::where('user_id', $userId)
            ->where('last_used_at', '>', now()->subDays(30))
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            Log::info("No active FCM tokens for user {$userId}");
            return ['success' => true, 'sent_count' => 0, 'failed_tokens' => []];
        }

        return $this->sendMulticast($tokens, $title, $body, $data, $userId, $lessonId);
    }

    /**
     * Invia notifica push a multipli token (multicast)
     *
     * @param array $tokens
     * @param string $title
     * @param string $body
     * @param array $data
     * @param int|null $userId
     * @param int|null $lessonId
     * @return array
     */
    public function sendMulticast(array $tokens, string $title, string $body, array $data = [], ?int $userId = null, ?int $lessonId = null): array
    {
        if (!$this->messaging) {
            return ['success' => false, 'sent_count' => 0, 'failed_tokens' => []];
        }

        $notification = Notification::create($title, $body);
        $failedTokens = [];
        $sentCount = 0;

        foreach ($tokens as $token) {
            try {
                $message = CloudMessage::withTarget('token', $token)
                    ->withNotification($notification)
                    ->withData($data);

                $this->messaging->send($message);
                $sentCount++;

                // Update last_used_at for successful send
                FcmToken::where('token', $token)->update(['last_used_at' => now()]);

                // Log successful notification
                $this->logNotification($userId, $lessonId, 'lesson_reminder', $title, $body, $data, 'sent');

                Log::info("Push notification sent successfully to token: " . substr($token, 0, 20) . "...");

            } catch (MessagingException $e) {
                $failedTokens[] = $token;

                // Check if token is invalid
                if ($e->errors()->containsUnregisteredToken() || $e->errors()->containsInvalidToken()) {
                    Log::warning("Invalid/unregistered token detected, removing: " . substr($token, 0, 20) . "...");
                    FcmToken::where('token', $token)->delete();
                }

                // Log failed notification
                $this->logNotification($userId, $lessonId, 'lesson_reminder', $title, $body, $data, 'failed', $e->getMessage());

                Log::error("Failed to send push notification: " . $e->getMessage());
            }
        }

        return [
            'success' => $sentCount > 0,
            'sent_count' => $sentCount,
            'failed_tokens' => $failedTokens,
        ];
    }

    /**
     * Log notification to database
     *
     * @param int|null $userId
     * @param int|null $lessonId
     * @param string $type
     * @param string $title
     * @param string $body
     * @param array $data
     * @param string $status
     * @param string|null $errorMessage
     * @return void
     */
    protected function logNotification(
        ?int $userId,
        ?int $lessonId,
        string $type,
        string $title,
        string $body,
        array $data,
        string $status,
        ?string $errorMessage = null
    ): void {
        try {
            NotificationLog::create([
                'user_id' => $userId,
                'lesson_id' => $lessonId,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'data' => $data,
                'status' => $status,
                'sent_at' => $status === 'sent' ? now() : null,
                'error_message' => $errorMessage,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to log notification: " . $e->getMessage());
        }
    }

    /**
     * Test connection Firebase
     *
     * @return bool
     */
    public function testConnection(): bool
    {
        return $this->messaging !== null;
    }
}
