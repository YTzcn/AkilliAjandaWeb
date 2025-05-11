<?php

namespace App\Helpers;

use App\Models\UserDevices;
use Illuminate\Support\Facades\Http;

class NotificationHelper
{
    public static function sendNotificationToUser($userId, $title, $body, $data = [])
    {
        try {
            // Kullanıcının tüm cihaz tokenlarını al
            $deviceTokens = UserDevices::where('user_id', $userId)
                ->pluck('device_token')
                ->toArray();

            if (empty($deviceTokens)) {
                return false;
            }

            $credentialsFilePath = storage_path('app\firebase\akilliajanda-ff6c6-06dc5a512cfd.json');
            $client = new \Google\Client();
            $client->setAuthConfig($credentialsFilePath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->refreshTokenWithAssertion();
            $accessToken = $client->getAccessToken()['access_token'];

            $successCount = 0;

            // Her bir token için ayrı istek gönder
            foreach ($deviceTokens as $token) {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ])->post('https://fcm.googleapis.com/v1/projects/akilliajanda-ff6c6/messages:send', [
                    'message' => [
                        'token' => $token, // tokens yerine token kullanıyoruz
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                        'data' => array_map(function ($value) {
                            return (string) $value; // Tüm değerleri string'e çevir
                        }, $data),
                        'android' => [
                            'priority' => 'high',
                        ],
                        'apns' => [
                            'headers' => [
                                'apns-priority' => '10',
                            ],
                            'payload' => [
                                'aps' => [
                                    'sound' => 'default',
                                    'badge' => 1,
                                ],
                            ],
                        ],
                    ]
                ]);

                \Log::info('FCM response for token: ' . substr($token, 0, 20) . '...', [
                    'response' => $response->json(),
                    'status' => $response->status()
                ]);

                if ($response->successful()) {
                    $successCount++;
                } elseif ($response->status() === 404 || str_contains($response->body(), 'registration-token-not-registered')) {
                    // Token artık geçerli değil, veritabanından silelim
                    UserDevices::where('device_token', $token)->delete();
                }
            }

            // En az bir cihaza başarıyla gönderildiyse true döndür
            return $successCount > 0;
        } catch (\Exception $e) {
            \Log::error('Bildirim gönderme hatası:', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'title' => $title,
                'body' => $body,
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
} 