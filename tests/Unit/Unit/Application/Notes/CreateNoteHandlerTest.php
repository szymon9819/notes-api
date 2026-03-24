<?php

declare(strict_types=1);

namespace Tests\Unit\Unit\Application\Notes;

use App\Application\Common\Contracts\DateTimeProvider;
use App\Application\Common\Contracts\TransactionManager;
use App\Application\Notes\Commands\CreateNote\CreateNoteCommand;
use App\Application\Notes\Commands\CreateNote\CreateNoteHandler;
use App\Application\Notes\Contracts\NoteCommandRepository;
use App\Application\Notes\Contracts\NoteQueryRepository;
use App\Application\Notes\DTO\NoteData;
use App\Application\Notes\DTO\UserData;
use App\Domain\Notes\Entities\Note as DomainNote;
use App\Domain\Notes\Enums\NoteStatus;
use App\Domain\Notes\Enums\PublicationReasonType;
use App\Domain\Notes\Exceptions\PublicationReasonRequired;
use App\Domain\Notes\ValueObjects\NoteId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class CreateNoteHandlerTest extends TestCase
{
    public function test_it_rejects_creating_a_published_note_without_a_publication_reason(): void
    {
        $noteCommandRepository = $this->createMock(NoteCommandRepository::class);
        $noteCommandRepository->expects($this->never())->method('save');
        $noteQueryRepository = $this->createStub(NoteQueryRepository::class);
        $dateTimeProvider = $this->createStub(DateTimeProvider::class);
        $dateTimeProvider->method('now')->willReturn(new DateTimeImmutable('2026-03-24T10:00:00+00:00'));

        $createNoteHandler = new CreateNoteHandler(
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

        $this->expectException(PublicationReasonRequired::class);

        $createNoteHandler->handle(new CreateNoteCommand(
            userId: 1,
            title: 'Publish the roadmap',
            content: 'Roadmap details.',
            status: NoteStatus::Published,
            isPinned: false,
            publishedAt: null,
            publicationReasonType: null,
            publicationReasonMessage: null,
            tagIds: [],
        ));
    }

    public function test_it_passes_a_normalized_publication_reason_to_the_repository(): void
    {
        $savedReasonMessage = null;
        $noteCommandRepository = $this->createMock(NoteCommandRepository::class);
        $noteCommandRepository
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (DomainNote $domainNote) use (&$savedReasonMessage): DomainNote {
                $savedReasonMessage = $domainNote->publicationReason()?->message();

                return DomainNote::reconstitute(
                    noteId: NoteId::fromInt(10),
                    userId: $domainNote->userId(),
                    title: $domainNote->title(),
                    content: $domainNote->content(),
                    noteStatus: $domainNote->status(),
                    isPinned: $domainNote->isPinned(),
                    publishedAt: $domainNote->publishedAt(),
                    publicationReason: $domainNote->publicationReason(),
                    tagIds: $domainNote->tagIds(),
                );
            });
        $noteQueryRepository = $this->createMock(NoteQueryRepository::class);
        $noteQueryRepository
            ->expects($this->once())
            ->method('findOwnedById')
            ->willReturn(new NoteData(
                id: 10,
                userId: 1,
                title: 'Publish the roadmap',
                content: 'Roadmap details.',
                status: 'published',
                isPinned: false,
                publishedAt: '2026-03-24T10:00:00+00:00',
                publicationReasonType: 'decision',
                publicationReasonMessage: 'Approved in planning review.',
                createdAt: '2026-03-24T10:00:00+00:00',
                updatedAt: '2026-03-24T10:00:00+00:00',
                user: new UserData(1, 'User', 'user@example.com'),
                tags: [],
            ));
        $dateTimeProvider = $this->createStub(DateTimeProvider::class);
        $dateTimeProvider->method('now')->willReturn(new DateTimeImmutable('2026-03-24T10:00:00+00:00'));

        $createNoteHandler = new CreateNoteHandler(
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

        $noteData = $createNoteHandler->handle(new CreateNoteCommand(
            userId: 1,
            title: 'Publish the roadmap',
            content: 'Roadmap details.',
            status: NoteStatus::Published,
            isPinned: false,
            publishedAt: null,
            publicationReasonType: PublicationReasonType::Decision,
            publicationReasonMessage: '  Approved in planning review.  ',
            tagIds: [],
        ));

        $this->assertSame('Approved in planning review.', $savedReasonMessage);
        $this->assertSame('decision', $noteData->publicationReasonType);
    }
}
