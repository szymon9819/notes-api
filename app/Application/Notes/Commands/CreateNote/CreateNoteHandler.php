<?php

declare(strict_types=1);

namespace App\Application\Notes\Commands\CreateNote;

use App\Application\Common\Contracts\DateTimeProvider;
use App\Application\Common\Contracts\TransactionManager;
use App\Application\Common\CQRS\CommandHandler;
use App\Application\Notes\Contracts\NoteCommandRepository;
use App\Application\Notes\Contracts\NoteQueryRepository;
use App\Application\Notes\DTO\NoteData;
use App\Application\Notes\Exceptions\NoteNotFound;
use App\Domain\Notes\Entities\Note;
use App\Domain\Notes\ValueObjects\PublicationReason;
use App\Domain\Notes\ValueObjects\TagId;

final readonly class CreateNoteHandler implements CommandHandler
{
    public function __construct(
        private NoteCommandRepository $noteCommandRepository,
        private NoteQueryRepository $noteQueryRepository,
        private DateTimeProvider $dateTimeProvider,
        private TransactionManager $transactionManager,
    ) {}

    public function handle(CreateNoteCommand $createNoteCommand): NoteData
    {
        $note = Note::create(
            userId: $createNoteCommand->userId,
            title: $createNoteCommand->title,
            content: $createNoteCommand->content,
            noteStatus: $createNoteCommand->status,
            isPinned: $createNoteCommand->isPinned,
            publishedAt: $createNoteCommand->publishedAt,
            publicationReason: PublicationReason::fromNullable(
                $createNoteCommand->publicationReasonType,
                $createNoteCommand->publicationReasonMessage,
            ),
            tagIds: array_map(
                TagId::fromInt(...),
                $createNoteCommand->tagIds,
            ),
        );

        $note->ensurePublicationRules();
        $note->ensurePublishedAt($this->dateTimeProvider->now());

        $persistedNote = $this->transactionManager->run(
            fn (): Note => $this->noteCommandRepository->save($note),
        );

        $noteData = $this->noteQueryRepository->findOwnedById($persistedNote->userId(), $persistedNote->id() ?? throw NoteNotFound::forCreatedNote());

        if (!$noteData instanceof NoteData) {
            throw NoteNotFound::forCreatedNote();
        }

        return $noteData;
    }
}
