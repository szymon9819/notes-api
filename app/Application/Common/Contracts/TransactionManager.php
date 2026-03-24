<?php

declare(strict_types=1);

namespace App\Application\Common\Contracts;

interface TransactionManager
{
    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public function run(callable $callback): mixed;
}
