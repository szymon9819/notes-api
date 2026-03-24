<?php

declare(strict_types=1);

namespace App\Application\Common\Contracts;

use DateTimeImmutable;

interface DateTimeProvider
{
    public function now(): DateTimeImmutable;
}
