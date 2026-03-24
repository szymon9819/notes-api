<?php

declare(strict_types=1);

namespace App\Application\Notes\DTO;

final readonly class UserData
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
    ) {}
}
