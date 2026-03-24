<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use App\Application\Auth\Contracts\ApiTokenIssuer;
use App\Application\Auth\DTO\IssuedTokenData;
use App\Domain\Auth\Entities\UserIdentity;
use App\Persistence\Eloquent\Models\User;
use Illuminate\Support\Carbon;

final class SanctumApiTokenIssuer implements ApiTokenIssuer
{
    public function issueFor(UserIdentity $userIdentity, string $deviceName): IssuedTokenData
    {
        /** @var User $eloquentUser */
        $eloquentUser = User::query()->findOrFail($userIdentity->id->value);

        $ttlInMinutes = (int) config('sanctum.expiration', 60);
        $issuedAt = Carbon::now();
        $expiresAt = $issuedAt->copy()->addMinutes($ttlInMinutes);
        $newAccessToken = $eloquentUser->createToken(
            name: $deviceName,
            expiresAt: $expiresAt,
        );

        return new IssuedTokenData(
            token: $newAccessToken->plainTextToken,
            tokenType: 'Bearer',
            expiresIn: (int) $issuedAt->diffInSeconds($expiresAt),
            expiresAt: $expiresAt->toAtomString(),
        );
    }
}
