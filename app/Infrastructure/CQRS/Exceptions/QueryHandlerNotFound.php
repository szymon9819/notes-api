<?php

declare(strict_types=1);

namespace App\Infrastructure\CQRS\Exceptions;

final class QueryHandlerNotFound extends CqrsConfigurationException
{
    public static function forQuery(string $queryClass, string $handlerClass): self
    {
        return new self(sprintf('Query handler [%s] was not found for query [%s].', $handlerClass, $queryClass));
    }
}
