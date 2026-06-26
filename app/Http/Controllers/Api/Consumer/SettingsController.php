<?php

namespace App\Http\Controllers\Api\Consumer;

use App\Http\Controllers\Controller;
use App\Support\ConsumerAppSettings;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => ConsumerAppSettings::publicPayload(),
        ]);
    }
}
