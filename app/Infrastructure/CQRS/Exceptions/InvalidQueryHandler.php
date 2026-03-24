<?php

declare(strict_types=1);

namespace App\Infrastructure\CQRS\Exceptions;

use App\Application\Common\CQRS\QueryHandler;

final class InvalidQueryHandler extends CqrsConfigurationException
{
    public static function becauseItMustImplementContract(string $handlerClass): self
    {
        return new self(sprintf('Query handler [%s] must implement [%s].', $handlerClass, QueryHandler::class));
    }

    public static function becauseHandleMethodIsMissing(string $handlerClass): self
    {
        return new self(sprintf('Query handler [%s] must expose a handle method.', $handlerClass));
    }
}
