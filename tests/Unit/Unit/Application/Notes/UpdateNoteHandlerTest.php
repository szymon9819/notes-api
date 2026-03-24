<?php

declare(strict_types=1);

namespace Tests\Unit\Unit\Application\Notes;

use App\Application\Common\Contracts\DateTimeProvider;
use App\Application\Common\Contracts\TransactionManager;
use App\Application\Notes\Commands\UpdateNote\UpdateNoteCommand;
use App\Application\Notes\Commands\UpdateNote\UpdateNoteHandler;
use App\Application\Notes\Contracts\NoteCommandRepository;
use App\Application\Notes\Contracts\NoteQueryRepository;
use App\Domain\Common\ValueObjects\UserId;
use App\Domain\Notes\Entities\Note;
use App\Domain\Notes\Enums\NoteStatus;
use App\Domain\Notes\Enums\PublicationReasonType;
use App\Domain\Notes\Exceptions\PublicationReasonCannotMatchTitle;
use App\Domain\Notes\ValueObjects\NoteId;
use App\Domain\Notes\ValueObjects\PublicationReason;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class UpdateNoteHandlerTest extends TestCase
{
    public function test_it_rejects_updating_a_title_to_match_the_existing_publication_reason(): void
    {
        $noteCommandRepository = $this->createMock(NoteCommandRepository::class);
        $noteCommandRepository
            ->expects($this->once())
            ->method('findOwnedById')
            ->willReturn(Note::reconstitute(
                noteId: NoteId::fromInt(10),
                userId: UserId::fromInt(1),
                title: 'Engineering update',
                content: 'Release notes.',
                noteStatus: NoteStatus::Published,
                isPinned: false,
                publishedAt: new DateTimeImmutable('2026-03-24T09:00:00+00:00'),
                publicationReason: new PublicationReason(
                    PublicationReasonType::Knowledge,
                    'Release notes',
                ),
                tagIds: [],
            ));
        $noteCommandRepository->expects($this->never())->method('save');
        $noteQueryRepository = $this->createStub(NoteQueryRepository::class);
        $dateTimeProvider = $this->createStub(DateTimeProvider::class);
        $dateTimeProvider->method('now')->willReturn(new DateTimeImmutable('2026-03-24T10:00:00+00:00'));

        $updateNoteHandler = new UpdateNoteHandler(
            $noteCommandRepository,
            $noteQueryRepository,
            $dateTimeProvider,
            new class() implements TransactionManager
            {
                public function run(callable $callback): mixed
                {
                    return $callback();
                }
            },
        );

        $this->expectException(PublicationReasonCannotMatchTitle::class);

        $updateNoteHandler->handle(new UpdateNoteCommand(
            userId: UserId::fromInt(1),
            noteId: NoteId::fromInt(10),
            content: null,
            title: 'Release notes',
            status: NoteStatus::Published,
            isPinned: false,
            publishedAt: new DateTimeImmutable('2026-03-24T09:00:00+00:00'),
            publicationReasonType: PublicationReasonType::Knowledge,
            publicationReasonMessage: 'Release notes',
            tagIds: [],
        ));
    }
}
