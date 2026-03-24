<?php

declare(strict_types=1);

namespace App\Infrastructure\CQRS;

use App\Application\Common\CQRS\Query;
use App\Application\Common\CQRS\QueryBus;
use App\Application\Common\CQRS\QueryHandler;
use App\Infrastructure\CQRS\Exceptions\InvalidQueryHandler;
use App\Infrastructure\CQRS\Exceptions\InvalidQueryNaming;
use App\Infrastructure\CQRS\Exceptions\QueryHandlerNotFound;
use Illuminate\Container\Container;

final readonly class ConventionQueryBus implements QueryBus
{
    public function __construct(
        private Container $container,
    ) {}

    public function ask(Query $query): mixed
    {
        $handlerClass = $this->resolveHandlerClass($query::class, 'Query');

        if (!class_exists($handlerClass)) {
            throw QueryHandlerNotFound::forQuery($query::class, $handlerClass);
        }

        $handler = $this->container->make($handlerClass);

        if (!$handler instanceof QueryHandler) {
            throw InvalidQueryHandler::becauseItMustImplementContract($handlerClass);
        }

        if (!method_exists($handler, 'handle')) {
            throw InvalidQueryHandler::becauseHandleMethodIsMissing($handlerClass);
        }

        return $handler->handle($query);
    }

    private function resolveHandlerClass(string $messageClass, string $messageSuffix): string
    {
        if (!str_ends_with($messageClass, $messageSuffix)) {
            throw InvalidQueryNaming::becauseSuffixIsMissing($messageClass);
        }

        return substr($messageClass, 0, -strlen($messageSuffix)) . 'Handler';
    }
}
