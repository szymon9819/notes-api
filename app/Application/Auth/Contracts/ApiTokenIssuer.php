<?php

declare(strict_types=1);

namespace App\Application\Auth\Contracts;

use App\Application\Auth\DTO\IssuedTokenData;
use App\Domain\Auth\Entities\UserIdentity;

interface ApiTokenIssuer
{
    public function issueFor(UserIdentity $userIdentity, string $deviceName): IssuedTokenData;
}
