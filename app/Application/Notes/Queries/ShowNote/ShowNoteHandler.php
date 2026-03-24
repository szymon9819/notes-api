<?php

declare(strict_types=1);

namespace App\Application\Notes\Queries\ShowNote;

use App\Application\Common\CQRS\QueryHandler;
use App\Application\Notes\Contracts\NoteQueryRepository;
use App\Application\Notes\DTO\NoteData;
use App\Application\Notes\Exceptions\NoteNotFound;

final readonly class ShowNoteHandler implements QueryHandler
{
    public function __construct(
        private NoteQueryRepository $noteQueryRepository,
    ) {}

    public function handle(ShowNoteQuery $showNoteQuery): NoteData
    {
        $note = $this->noteQueryRepository->findOwnedById(
            $showNoteQuery->userId,
            $showNoteQuery->noteId,
        );

        if (!$note instanceof NoteData) {
            throw NoteNotFound::forId($showNoteQuery->noteId->value);
        }

        return $note;
    }
}
