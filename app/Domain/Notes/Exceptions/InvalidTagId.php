<?php

declare(strict_types=1);

namespace App\Domain\Notes\Exceptions;

use InvalidArgumentException;

final class InvalidTagId extends InvalidArgumentException
{
    public static function becauseItMustBePositive(int $value): self
    {
        return new self(sprintf('Tag ID must be a positive integer, [%d] given.', $value));
    }
}
