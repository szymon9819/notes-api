<?php

declare(strict_types=1);

namespace App\Application\Notes\Commands\DeleteNote;

use App\Application\Common\CQRS\Command;
use App\Domain\Common\ValueObjects\UserId;
use App\Domain\Notes\ValueObjects\NoteId;

final readonly class DeleteNoteCommand implements Command
{
    public function __construct(
        public UserId $userId,
        public NoteId $noteId,
    ) {}
}
