<?php

declare(strict_types=1);

namespace App\Domain\Common\Exceptions;

use InvalidArgumentException;

final class InvalidUserId extends InvalidArgumentException
{
    public static function becauseItMustBePositive(int $value): self
    {
        return new self(sprintf('User ID must be a positive integer, [%d] given.', $value));
    }
}
