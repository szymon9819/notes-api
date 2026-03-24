<?php

declare(strict_types=1);

namespace App\Domain\Notes\Exceptions;

use InvalidArgumentException;

final class InvalidPublicationReasonMessage extends InvalidArgumentException
{
    public static function becauseItIsEmpty(): self
    {
        return new self('Publication reason message cannot be empty.');
    }

    public static function becauseItExceedsAllowedLength(int $allowedLength): self
    {
        return new self(sprintf('Publication reason message cannot exceed %d characters.', $allowedLength));
    }

    public static function becauseItContainsUrl(): self
    {
        return new self('Publication reason message cannot contain a URL.');
    }
}
