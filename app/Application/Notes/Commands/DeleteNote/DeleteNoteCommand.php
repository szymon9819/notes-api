<?php

declare(strict_types=1);

namespace App\Application\Notes\Commands\DeleteNote;

use App\Application\Common\CQRS\Command;

final readonly class DeleteNoteCommand implements Command
{
    public function __construct(
        public int $userId,
        public int $noteId,
    ) {}
}
