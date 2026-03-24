<?php

declare(strict_types=1);

namespace App\Domain\Common\ValueObjects;

use InvalidArgumentException;

abstract readonly class IntId
{
    final protected function __construct(
        public int $value,
    ) {
        if ($this->value < 1) {
            throw static::invalidBecauseItMustBePositive($this->value);
        }
    }

    abstract public static function fromInt(int $value): static;

    abstract protected static function invalidBecauseItMustBePositive(int $value): InvalidArgumentException;
}
