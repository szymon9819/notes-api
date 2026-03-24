<?php

declare(strict_types=1);

namespace App\Application\Auth\DTO;

final readonly class IssuedTokenData
{
    public function __construct(
        public string $token,
        public string $tokenType,
        public int $expiresIn,
        public string $expiresAt,
    ) {}
}
