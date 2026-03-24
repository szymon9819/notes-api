<?php

declare(strict_types=1);

namespace App\Domain\Notes\ValueObjects;

use App\Domain\Common\ValueObjects\IntId;
use App\Domain\Notes\Exceptions\InvalidNoteId;

final readonly class NoteId extends IntId
{
    public static function fromInt(int $value): static
    {
        return new self($value);
    }

    protected static function invalidBecauseItMustBePositive(int $value): InvalidNoteId
    {
        return InvalidNoteId::becauseItMustBePositive($value);
    }
}
