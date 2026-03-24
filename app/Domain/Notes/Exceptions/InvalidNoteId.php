<?php

declare(strict_types=1);

namespace App\Domain\Notes\Exceptions;

use InvalidArgumentException;

final class InvalidNoteId extends InvalidArgumentException
{
    public static function becauseItMustBePositive(int $value): self
    {
        return new self(sprintf('Note ID must be a positive integer, [%d] given.', $value));
    }
}
