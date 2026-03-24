<?php

declare(strict_types=1);

namespace Tests\Unit\Unit\Application\Notes;

use App\Application\Notes\Contracts\NoteQueryRepository;
use App\Application\Notes\DTO\PaginatedData;
use App\Application\Notes\Queries\ListNotes\ListNotesHandler;
use App\Application\Notes\Queries\ListNotes\ListNotesQuery;
use PHPUnit\Framework\TestCase;

final class ListNotesHandlerTest extends TestCase
{
    public function test_it_returns_paginated_notes_from_the_repository(): void
    {
        $paginatedData = new PaginatedData([], [], []);
        $noteQueryRepository = $this->createMock(NoteQueryRepository::class);
        $noteQueryRepository
            ->expects($this->once())
            ->method('paginateOwnedBy')
            ->willReturn($paginatedData);

        $listNotesHandler = new ListNotesHandler($noteQueryRepository);

        $result = $listNotesHandler->handle(new ListNotesQuery(
            userId: 1,
            perPage: 15,
        ));

        $this->assertSame($paginatedData, $result);
    }
}
