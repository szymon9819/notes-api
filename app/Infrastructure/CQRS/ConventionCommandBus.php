<?php

declare(strict_types=1);

namespace App\Infrastructure\CQRS;

use App\Application\Common\CQRS\Command;
use App\Application\Common\CQRS\CommandBus;
use App\Application\Common\CQRS\CommandHandler;
use App\Infrastructure\CQRS\Exceptions\CommandHandlerNotFound;
use App\Infrastructure\CQRS\Exceptions\InvalidCommandHandler;
use App\Infrastructure\CQRS\Exceptions\InvalidCommandNaming;
use Illuminate\Container\Container;

final readonly class ConventionCommandBus implements CommandBus
{
    public function __construct(
        private Container $container,
    ) {}

    public function dispatch(Command $command): mixed
    {
        $handlerClass = $this->resolveHandlerClass($command::class, 'Command');

        if (!class_exists($handlerClass)) {
            throw CommandHandlerNotFound::forCommand($command::class, $handlerClass);
        }

        $handler = $this->container->make($handlerClass);

        if (!$handler instanceof CommandHandler) {
            throw InvalidCommandHandler::becauseItMustImplementContract($handlerClass);
        }

        if (!method_exists($handler, 'handle')) {
            throw InvalidCommandHandler::becauseHandleMethodIsMissing($handlerClass);
        }

        return $handler->handle($command);
    }

    private function resolveHandlerClass(string $messageClass, string $messageSuffix): string
    {
        if (!str_ends_with($messageClass, $messageSuffix)) {
            throw InvalidCommandNaming::becauseSuffixIsMissing($messageClass);
        }

        return substr($messageClass, 0, -strlen($messageSuffix)) . 'Handler';
    }
}
