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
use App\Domain\Common\ValueObjects\UserId;
use App\Domain\Notes\Entities\Note;
use App\Domain\Notes\Enums\NoteStatus;
use App\Domain\Notes\ValueObjects\NoteId;
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
        $userId = UserId::fromInt($updateNoteCommand->userId);
        $noteId = NoteId::fromInt($updateNoteCommand->noteId);
        $note = $this->noteCommandRepository->findOwnedById($userId, $noteId);

        if (!$note instanceof Note) {
            throw NoteNotFound::forId($updateNoteCommand->noteId);
        }

        if ($updateNoteCommand->hasTitle && $updateNoteCommand->title !== null) {
            $note->changeTitle($updateNoteCommand->title);
        }

        if ($updateNoteCommand->hasContent) {
            $note->changeContent($updateNoteCommand->content);
        }

        if ($updateNoteCommand->hasStatus && $updateNoteCommand->status instanceof NoteStatus) {
            $note->changeStatus($updateNoteCommand->status);
        }

        if ($updateNoteCommand->hasPinnedState) {
            $note->changePinnedState($updateNoteCommand->isPinned);
        }

        if ($updateNoteCommand->hasPublishedAt) {
            $note->changePublishedAt($updateNoteCommand->publishedAt);
        }

        if ($updateNoteCommand->hasPublicationReasonType || $updateNoteCommand->hasPublicationReasonMessage) {
            $publicationReasonType = $updateNoteCommand->hasPublicationReasonType
                ? $updateNoteCommand->publicationReasonType
                : $note->publicationReason()?->type();
            $publicationReasonMessage = $updateNoteCommand->hasPublicationReasonMessage
                ? $updateNoteCommand->publicationReasonMessage
                : $note->publicationReason()?->message();

            $note->changePublicationReason(PublicationReason::fromNullable(
                $publicationReasonType,
                $publicationReasonMessage,
            ));
        }

        if ($updateNoteCommand->hasTagIds) {
            $note->syncTags(array_map(
                TagId::fromInt(...),
                $updateNoteCommand->tagIds,
            ));
        }

        $note->ensurePublicationRules();
        $note->ensurePublishedAt($this->dateTimeProvider->now());

        $this->transactionManager->run(
            fn (): Note => $this->noteCommandRepository->save($note),
        );

        $noteData = $this->noteQueryRepository->findOwnedById($userId, $noteId);

        if (!$noteData instanceof NoteData) {
            throw NoteNotFound::forId($updateNoteCommand->noteId);
        }

        return $noteData;
    }
}
