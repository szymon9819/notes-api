<?php

declare(strict_types=1);

namespace Tests\Unit\Unit\Application\Notes;

use App\Application\Notes\Contracts\NoteQueryRepository;
use App\Application\Notes\DTO\NoteData;
use App\Application\Notes\DTO\UserData;
use App\Application\Notes\Queries\ShowNote\ShowNoteHandler;
use App\Application\Notes\Queries\ShowNote\ShowNoteQuery;
use App\Domain\Common\ValueObjects\UserId;
use App\Domain\Notes\ValueObjects\NoteId;
use PHPUnit\Framework\TestCase;

final class ShowNoteHandlerTest extends TestCase
{
    public function test_it_returns_the_owned_note(): void
    {
        $noteData = new NoteData(
            id: 10,
            userId: 1,
            title: 'Release note',
            content: 'Details.',
            status: 'published',
            isPinned: false,
            publishedAt: '2026-03-24T10:00:00+00:00',
            publicationReasonType: 'knowledge',
            publicationReasonMessage: 'Share release details.',
            createdAt: '2026-03-24T10:00:00+00:00',
            updatedAt: '2026-03-24T10:00:00+00:00',
            user: new UserData(1, 'User', 'user@example.com'),
            tags: [],
        );
        $noteQueryRepository = $this->createMock(NoteQueryRepository::class);
        $noteQueryRepository
            ->expects($this->once())
            ->method('findOwnedById')
            ->willReturn($noteData);

        $showNoteHandler = new ShowNoteHandler($noteQueryRepository);

        $result = $showNoteHandler->handle(new ShowNoteQuery(
            userId: UserId::fromInt(1),
            noteId: NoteId::fromInt(10),
        ));

        $this->assertSame($noteData, $result);
    }
}
