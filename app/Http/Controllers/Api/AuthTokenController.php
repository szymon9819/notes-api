<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IssueApiTokenRequest;
use App\Models\User;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

#[Group('Authentication', 'Authentication endpoints for the demo API.', 5)]
class AuthTokenController extends Controller
{
    #[Endpoint('login', 'Login', 'Authenticates the user and returns a Sanctum bearer token with TTL.')]
    public function login(IssueApiTokenRequest $issueApiTokenRequest): JsonResponse
    {
        $user = User::query()->where('email', $issueApiTokenRequest->email())->first();

        if (!($user instanceof User) || !Hash::check($issueApiTokenRequest->password(), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $ttlInMinutes = (int) config('sanctum.expiration', 60);
        $issuedAt = Carbon::now();
        $expiresAt = $issuedAt->copy()->addMinutes($ttlInMinutes);
        $expiresInSeconds = $issuedAt->diffInSeconds($expiresAt);
        $newAccessToken = $user->createToken(
            name: $issueApiTokenRequest->deviceName(),
            expiresAt: $expiresAt,
        );

        return response()->json([
            'token' => $newAccessToken->plainTextToken,
            'token_type' => 'Bearer',
            'expires_in' => $expiresInSeconds,
            'expires_at' => $expiresAt->toAtomString(),
        ]);
    }
}
