<?php

namespace App\Http\Controllers;

use App\Models\UserDevices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceController extends Controller
{
    public function saveToken(Request $request)
    {
        $request->validate([
            'device_token' => 'required|string'
        ]);

        try {
            $userDevice = UserDevices::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'device_token' => $request->device_token
                ],
                [
                    'device_type' => 'web',
                    'last_used_at' => now()
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Device token saved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save device token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteToken(Request $request)
    {
        $request->validate([
            'device_token' => 'required|string'
        ]);

        try {
            // Sadece mevcut kullanıcının bu token'ını sil
            UserDevices::where('user_id', Auth::id())
                     ->where('device_token', $request->device_token)
                     ->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Device token deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete device token',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 