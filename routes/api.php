<?php

declare(strict_types=1);

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function (): JsonResponse {
    return response()->json([
        'status' => 'ok',
    ]);
});

Route::get('/user', fn (Request $request) => $request->user())->middleware('auth:sanctum');
