<?php

declare(strict_types=1);

namespace App\Infrastructure\Presentation\Api;

use App\Application\Auth\DTO\IssuedTokenData;

final class IssuedTokenPresenter
{
    /**
     * @return array{token: string, token_type: string, expires_in: int, expires_at: string}
     */
    public function present(IssuedTokenData $issuedTokenData): array
    {
        return [
            'token' => $issuedTokenData->token,
            'token_type' => $issuedTokenData->tokenType,
            'expires_in' => $issuedTokenData->expiresIn,
            'expires_at' => $issuedTokenData->expiresAt,
        ];
    }
}
