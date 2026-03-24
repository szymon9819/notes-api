<?php

declare(strict_types=1);

namespace App\Application\Auth\Contracts;

use App\Application\Auth\Exceptions\InvalidCredentials;
use App\Domain\Auth\Entities\UserIdentity;

interface UserCredentialsGateway
{
    /**
     * @throws InvalidCredentials
     */
    public function authenticate(string $email, string $password): UserIdentity;
}
