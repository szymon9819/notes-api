<?php

declare(strict_types=1);

namespace App\Application\Notes\Queries\ShowNote;

use App\Application\Common\CQRS\Query;

final readonly class ShowNoteQuery implements Query
{
    public function __construct(
        public int $userId,
        public int $noteId,
    ) {}
}
