<?php

declare(strict_types=1);

namespace App\Domain\Auth\Entities;

use App\Domain\Common\ValueObjects\UserId;

final readonly class UserIdentity
{
    public function __construct(
        public UserId $id,
        public string $name,
        public string $email,
    ) {}
}
