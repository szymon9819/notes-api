<?php

declare(strict_types=1);

namespace App\Application\Notes\Contracts;

use App\Application\Notes\DTO\NoteData;
use App\Application\Notes\DTO\PaginatedData;
use App\Application\Notes\Queries\ListNotes\ListNotesQuery;
use App\Domain\Common\ValueObjects\UserId;
use App\Domain\Notes\ValueObjects\NoteId;

interface NoteQueryRepository
{
    /**
     * @return PaginatedData<NoteData>
     */
    public function paginateOwnedBy(ListNotesQuery $listNotesQuery): PaginatedData;

    public function findOwnedById(UserId $userId, NoteId $noteId): ?NoteData;
}
