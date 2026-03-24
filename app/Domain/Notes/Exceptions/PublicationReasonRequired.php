<?php

declare(strict_types=1);

namespace App\Domain\Notes\Exceptions;

use DomainException;

final class PublicationReasonRequired extends DomainException
{
    public static function forPublishedNote(): self
    {
        return new self('A publication reason is required for published notes.');
    }
}
