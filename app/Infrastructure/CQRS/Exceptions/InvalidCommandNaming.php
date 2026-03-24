<?php

declare(strict_types=1);

namespace App\Infrastructure\CQRS\Exceptions;

final class InvalidCommandNaming extends CqrsConfigurationException
{
    public static function becauseSuffixIsMissing(string $commandClass): self
    {
        return new self(sprintf('Command [%s] must end with [Command] to resolve its handler by convention.', $commandClass));
    }
}
