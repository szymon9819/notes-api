<?php

declare(strict_types=1);

namespace App\Application\Notes\Queries\ShowNote;

use App\Application\Common\CQRS\Query;
use App\Domain\Common\ValueObjects\UserId;
use App\Domain\Notes\ValueObjects\NoteId;

final readonly class ShowNoteQuery implements Query
{
    public function __construct(
        public UserId $userId,
        public NoteId $noteId,
    ) {}
}
