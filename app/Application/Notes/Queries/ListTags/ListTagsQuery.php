<?php

declare(strict_types=1);

namespace App\Application\Notes\Queries\ListTags;

use App\Application\Common\CQRS\Query;

final readonly class ListTagsQuery implements Query
{
    public function __construct(
        public int $userId,
    ) {}
}
