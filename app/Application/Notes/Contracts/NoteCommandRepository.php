<?php

declare(strict_types=1);

namespace App\Application\Notes\Contracts;

use App\Domain\Common\ValueObjects\UserId;
use App\Domain\Notes\Entities\Note;
use App\Domain\Notes\ValueObjects\NoteId;

interface NoteCommandRepository
{
    public function findOwnedById(UserId $userId, NoteId $noteId): ?Note;

    public function save(Note $note): Note;

    public function deleteOwnedById(UserId $userId, NoteId $noteId): bool;
}
