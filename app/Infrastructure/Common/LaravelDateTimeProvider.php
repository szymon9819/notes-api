<?php

declare(strict_types=1);

namespace App\Infrastructure\Common;

use App\Application\Common\Contracts\DateTimeProvider;
use DateTimeImmutable;

final class LaravelDateTimeProvider implements DateTimeProvider
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now');
    }
}
