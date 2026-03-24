<?php

declare(strict_types=1);

namespace App\Application\Notes\Queries\ListNotes;

use App\Application\Common\CQRS\Query;

final readonly class ListNotesQuery implements Query
{
    public function __construct(
        public int $userId,
        public int $perPage,
        public ?string $search = null,
        public ?string $status = null,
        public ?string $tag = null,
        public ?bool $pinned = null,
    ) {}
}
