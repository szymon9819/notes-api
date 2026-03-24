<?php

declare(strict_types=1);

namespace App\Application\Common\CQRS;

interface CommandBus
{
    public function dispatch(Command $command): mixed;
}
