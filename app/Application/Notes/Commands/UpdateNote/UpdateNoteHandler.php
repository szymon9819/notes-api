<?php

declare(strict_types=1);

namespace App\Application\Notes\Commands\UpdateNote;

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

final readonly class UpdateNoteHandler implements CommandHandler
{
    public function __construct(
        private NoteCommandRepository $noteCommandRepository,
        private NoteQueryRepository $noteQueryRepository,
        private DateTimeProvider $dateTimeProvider,
        private TransactionManager $transactionManager,
    ) {}

    public function handle(UpdateNoteCommand $updateNoteCommand): NoteData
    {
        $userId = $updateNoteCommand->userId;
        $noteId = $updateNoteCommand->noteId;
        $note = $this->noteCommandRepository->findOwnedById($userId, $noteId);

        if (!$note instanceof Note) {
            throw NoteNotFound::forId($updateNoteCommand->noteId->value);
        }

        $note->changeTitle($updateNoteCommand->title);
        $note->changeContent($updateNoteCommand->content);
        $note->changeStatus($updateNoteCommand->status);
        $note->changePinnedState($updateNoteCommand->isPinned);
        $note->changePublishedAt($updateNoteCommand->publishedAt);
        $note->changePublicationReason(PublicationReason::fromNullable(
            $updateNoteCommand->publicationReasonType,
            $updateNoteCommand->publicationReasonMessage,
        ));
        $note->syncTags(array_map(
            TagId::fromInt(...),
            $updateNoteCommand->tagIds,
        ));

        $note->ensurePublicationRules();
        $note->ensurePublishedAt($this->dateTimeProvider->now());

        $this->transactionManager->run(
            fn (): Note => $this->noteCommandRepository->save($note),
        );

        $noteData = $this->noteQueryRepository->findOwnedById($userId, $noteId);

        if (!$noteData instanceof NoteData) {
            throw NoteNotFound::forId($updateNoteCommand->noteId->value);
        }

        return $noteData;
    }
}
