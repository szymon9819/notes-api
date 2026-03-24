<?php

declare(strict_types=1);

namespace App\Application\Common\Exceptions;

use Throwable;

final class TransactionFailed extends ApplicationException
{
    public static function because(Throwable $throwable): self
    {
        return new self('Database transaction failed.', previous: $throwable);
    }
}
