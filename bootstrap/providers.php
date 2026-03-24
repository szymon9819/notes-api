<?php

declare(strict_types=1);

use App\Infrastructure\Providers\CleanArchitectureServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    CleanArchitectureServiceProvider::class,
];
