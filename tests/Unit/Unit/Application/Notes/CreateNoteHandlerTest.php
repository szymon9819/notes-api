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
use App\Application\Notes\DTO\PaginatedData;
use App\Application\Notes\DTO\UserData;
use App\Application\Notes\Exceptions\NoteNotFound;
use App\Application\Notes\Queries\ListNotes\ListNotesQuery;
use App\Domain\Common\ValueObjects\UserId;
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

    public function test_it_returns_normalized_publication_reason_data(): void
    {
        $inMemoryNoteCommandRepository = new InMemoryNoteCommandRepository();
        $inMemoryNoteQueryRepository = new InMemoryNoteQueryRepository($inMemoryNoteCommandRepository);
        $dateTimeProvider = $this->createStub(DateTimeProvider::class);
        $dateTimeProvider->method('now')->willReturn(new DateTimeImmutable('2026-03-24T10:00:00+00:00'));

        $createNoteHandler = new CreateNoteHandler(
            $inMemoryNoteCommandRepository,
            $inMemoryNoteQueryRepository,
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

        $this->assertSame('Approved in planning review.', $noteData->publicationReasonMessage);
    }
}

final class InMemoryNoteCommandRepository implements NoteCommandRepository
{
    private ?DomainNote $domainNote = null;

    public function findOwnedById(UserId $userId, NoteId $noteId): ?DomainNote
    {
        return $this->domainNote;
    }

    public function save(DomainNote $domainNote): DomainNote
    {
        $this->domainNote = DomainNote::reconstitute(
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

        return $this->domainNote;
    }

    public function deleteOwnedById(UserId $userId, NoteId $noteId): bool
    {
        return false;
    }

    public function savedNote(): ?DomainNote
    {
        return $this->domainNote;
    }
}

final readonly class InMemoryNoteQueryRepository implements NoteQueryRepository
{
    public function __construct(private InMemoryNoteCommandRepository $inMemoryNoteCommandRepository) {}

    public function paginateOwnedBy(ListNotesQuery $listNotesQuery): PaginatedData
    {
        return new PaginatedData([], [], []);
    }

    public function findOwnedById(UserId $userId, NoteId $noteId): NoteData
    {
        $note = $this->inMemoryNoteCommandRepository->savedNote();

        if (!$note instanceof DomainNote) {
            throw NoteNotFound::forId($noteId->value);
        }

        return new NoteData(
            id: $noteId->value,
            userId: $userId->value,
            title: $note->title(),
            content: $note->content(),
            status: $note->status()->value,
            isPinned: $note->isPinned(),
            publishedAt: $note->publishedAt()?->format(DATE_ATOM),
            publicationReasonType: $note->publicationReason()?->type()->value,
            publicationReasonMessage: $note->publicationReason()?->message(),
            createdAt: null,
            updatedAt: null,
            user: new UserData($userId->value, 'User', 'user@example.com'),
            tags: [],
        );
    }
}
