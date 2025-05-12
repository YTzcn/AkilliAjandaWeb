<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserDevices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Cihaz Yönetimi",
 *     description="FCM (Firebase Cloud Messaging) token yönetimi için API endpoint'leri"
 * )
 */
class DeviceController extends Controller
{
    /**
     * FCM token'ı kaydeder veya günceller
     * 
     * @OA\Post(
     *     path="/api/device/token",
     *     tags={"Cihaz Yönetimi"},
     *     summary="FCM token'ı kaydeder veya günceller",
     *     description="Kullanıcının cihazı için FCM token'ı kaydeder. Aynı cihaz tipi için önceki kayıt varsa günceller.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"device_token", "device_type"},
     *             @OA\Property(
     *                 property="device_token",
     *                 type="string",
     *                 maxLength=255,
     *                 description="Firebase Cloud Messaging (FCM) token",
     *                 example="fMEI3X...Kg5:APA91b..."
     *             ),
     *             @OA\Property(
     *                 property="device_type",
     *                 type="string",
     *                 enum={"android", "ios", "web"},
     *                 description="Cihaz tipi",
     *                 example="android"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token başarıyla kaydedildi",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="FCM token başarıyla kaydedildi"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="device_token", type="string", example="fMEI3X...Kg5:APA91b..."),
     *                 @OA\Property(property="device_type", type="string", example="android"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Geçersiz veri",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Geçersiz veri"
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="device_token",
     *                     type="array",
     *                     @OA\Items(type="string", example="device_token alanı zorunludur")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Yetkisiz erişim",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthenticated."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Sunucu hatası",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="FCM token kaydedilirken bir hata oluştu"
     *             ),
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Internal server error"
     *             )
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateToken(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'device_token' => 'required|string|max:255',
                'device_type' => 'required|string|in:android,ios,web'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Geçersiz veri',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Mevcut token'ı kontrol et ve güncelle veya yeni kayıt oluştur
            $device = UserDevices::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'device_type' => $request->device_type
                ],
                [
                    'device_token' => $request->device_token
                ]
            );

            return response()->json([
                'message' => 'FCM token başarıyla kaydedildi',
                'data' => $device
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'FCM token kaydedilirken bir hata oluştu',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 