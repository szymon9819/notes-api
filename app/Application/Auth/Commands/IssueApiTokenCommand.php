<?php

declare(strict_types=1);

namespace App\Application\Auth\Commands;

use App\Application\Common\CQRS\Command;

final readonly class IssueApiTokenCommand implements Command
{
    public function __construct(
        public string $email,
        public string $password,
        public string $deviceName,
    ) {}
}
