<?php

declare(strict_types=1);

namespace App\Persistence\Database;

use App\Application\Common\Contracts\TransactionManager;
use App\Application\Common\Exceptions\TransactionFailed;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class LaravelTransactionManager implements TransactionManager
{
    public function __construct(
        private int $attempts = 3,
    ) {}

    public function run(callable $callback): mixed
    {
        $attempts = max(1, $this->attempts);

        try {
            return DB::transaction(
                static fn (Connection $connection): mixed => $callback(),
                attempts: $attempts,
            );
        } catch (Throwable $throwable) {
            throw TransactionFailed::because($throwable);
        }
    }
}
