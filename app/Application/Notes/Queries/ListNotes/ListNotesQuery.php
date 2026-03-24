<?php

declare(strict_types=1);

namespace App\Application\Notes\Queries\ListNotes;

use App\Application\Common\CQRS\Query;
use App\Domain\Common\ValueObjects\UserId;

final readonly class ListNotesQuery implements Query
{
    public function __construct(
        public UserId $userId,
        public int $perPage,
        public ?string $search = null,
        public ?string $status = null,
        public ?string $tag = null,
        public ?bool $pinned = null,
    ) {}
}
