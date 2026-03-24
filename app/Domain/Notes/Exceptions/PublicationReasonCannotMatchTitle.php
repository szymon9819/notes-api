<?php

declare(strict_types=1);

namespace App\Domain\Notes\Exceptions;

use DomainException;

final class PublicationReasonCannotMatchTitle extends DomainException
{
    public static function becauseMessageMatchesTitle(): self
    {
        return new self('Publication reason message cannot be identical to the note title.');
    }
}
