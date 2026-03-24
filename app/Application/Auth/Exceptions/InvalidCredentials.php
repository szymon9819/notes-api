<?php

declare(strict_types=1);

namespace App\Application\Auth\Exceptions;

use App\Application\Common\Exceptions\ApplicationException;

final class InvalidCredentials extends ApplicationException
{
    public static function forEmail(string $email): self
    {
        return new self(sprintf('Invalid credentials provided for [%s].', $email));
    }
}
