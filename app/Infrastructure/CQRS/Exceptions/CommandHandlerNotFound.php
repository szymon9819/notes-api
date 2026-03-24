<?php

declare(strict_types=1);

namespace App\Infrastructure\CQRS\Exceptions;

final class CommandHandlerNotFound extends CqrsConfigurationException
{
    public static function forCommand(string $commandClass, string $handlerClass): self
    {
        return new self(sprintf('Command handler [%s] was not found for command [%s].', $handlerClass, $commandClass));
    }
}
