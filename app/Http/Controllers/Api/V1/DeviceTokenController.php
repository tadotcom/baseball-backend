<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeviceTokenRequest; //
use App\Services\DeviceTokenService; //
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DeviceTokenController extends Controller
{
    protected DeviceTokenService $deviceTokenService;

    public function __construct(DeviceTokenService $deviceTokenService)
    {
        $this->deviceTokenService = $deviceTokenService;
    }

    /**
     * Store or update a device token for the authenticated user.
     *
     */
    public function store(StoreDeviceTokenRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $user = $request->user();

        try {
            $this->deviceTokenService->registerOrUpdateToken(
                $user,
                $validatedData['token'],
                $validatedData['device_type']
            ); // Service handles DB upsert

            return response()->json([
                'data' => ['message' => 'Device token registered successfully.'],
                'meta' => ['timestamp' => now()->toIso8601String()]
            ], 200); // 200 OK (or 201 if always creating)

        } catch (\Exception $e) {
            // Log error but don't necessarily abort app flow
            \Log::error('Failed to register device token', [
                'user_id' => $user->user_id,
                'error' => $e->getMessage()
            ]);
            // Return success even if DB fails? Or return error? Design decision.
            // Returning success to avoid blocking client.
             return response()->json([
                 'data' => ['message' => 'Device token registration acknowledged (processing may fail).'],
                 'meta' => ['timestamp' => now()->toIso8601String()]
             ], 202); // 202 Accepted might be appropriate
            // throw $e; // Or rethrow to return 500
        }
    }
}