<?php

declare(strict_types=1);

namespace App\Domain\Common\ValueObjects;

use App\Domain\Common\Exceptions\InvalidUserId;

final readonly class UserId extends IntId
{
    public static function fromInt(int $value): static
    {
        return new self($value);
    }

    protected static function invalidBecauseItMustBePositive(int $value): InvalidUserId
    {
        return InvalidUserId::becauseItMustBePositive($value);
    }
}
