<?php

namespace App\Helpers;


class FCMHelper
{
    protected $messaging;

    public static function sendPushNotification($user_id, $title, $message)
    {
        $credentialsFilePath = storage_path('json/firebase/autoMlFirebase.json');

        $client = new GoogleClient();

        $client->setAuthConfig($credentialsFilePath);

        $userTokens = UserDevice::where('user_id', $user_id)->get()->pluck('device_id')->toArray();

        if (empty($userTokens)) {
            return ['error' => 'No device tokens found for this user'];
        }

        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();
        $token = $client->getAccessToken();
        $access_token = $token['access_token'];

        $headers = [
            "Authorization: Bearer $access_token",
            'Content-Type: application/json'
        ];

        $data = [
            "message" => [
                "tokens" => $userTokens,
                "notification" => [
                    "title" => $title,
                    "body" => $message,
                ],
                "android" => [
                    "priority" => "high",
                ],
                "apns" => [
                    "headers" => [
                        "apns-priority" => "10",
                    ],
                ],
            ]
        ];

        $payload = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/v1/projects/automl-da54b/messages:send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return ['error' => $err];
        }

        return json_decode($response, true);
    }
}
