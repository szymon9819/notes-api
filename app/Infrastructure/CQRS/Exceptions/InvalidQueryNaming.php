<?php

declare(strict_types=1);

namespace App\Infrastructure\CQRS\Exceptions;

final class InvalidQueryNaming extends CqrsConfigurationException
{
    public static function becauseSuffixIsMissing(string $queryClass): self
    {
        return new self(sprintf('Query [%s] must end with [Query] to resolve its handler by convention.', $queryClass));
    }
}
