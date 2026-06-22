<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class ConfigApiController extends Controller
{
    public function stripe(): JsonResponse
    {
        return response()->json([
            'publicKey' => config('services.stripe.public_key'),
        ]);
    }
}
