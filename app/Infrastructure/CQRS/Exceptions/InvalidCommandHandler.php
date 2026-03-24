<?php

declare(strict_types=1);

namespace App\Infrastructure\CQRS\Exceptions;

use App\Application\Common\CQRS\CommandHandler;

final class InvalidCommandHandler extends CqrsConfigurationException
{
    public static function becauseItMustImplementContract(string $handlerClass): self
    {
        return new self(sprintf('Command handler [%s] must implement [%s].', $handlerClass, CommandHandler::class));
    }

    public static function becauseHandleMethodIsMissing(string $handlerClass): self
    {
        return new self(sprintf('Command handler [%s] must expose a handle method.', $handlerClass));
    }
}
