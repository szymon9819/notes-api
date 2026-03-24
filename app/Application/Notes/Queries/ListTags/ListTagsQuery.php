<?php

declare(strict_types=1);

namespace App\Application\Notes\Queries\ListTags;

use App\Application\Common\CQRS\Query;
use App\Domain\Common\ValueObjects\UserId;

final readonly class ListTagsQuery implements Query
{
    public function __construct(
        public UserId $userId,
    ) {}
}
