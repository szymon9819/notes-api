<?php

declare(strict_types=1);

namespace App\Application\Notes\Queries\ListNotes;

use App\Application\Common\CQRS\QueryHandler;
use App\Application\Notes\Contracts\NoteQueryRepository;
use App\Application\Notes\DTO\NoteData;
use App\Application\Notes\DTO\PaginatedData;

final readonly class ListNotesHandler implements QueryHandler
{
    public function __construct(
        private NoteQueryRepository $noteQueryRepository,
    ) {}

    /**
     * @return PaginatedData<NoteData>
     */
    public function handle(ListNotesQuery $listNotesQuery): PaginatedData
    {
        return $this->noteQueryRepository->paginateOwnedBy($listNotesQuery);
    }
}
