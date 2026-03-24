<?php

declare(strict_types=1);

namespace App\Persistence\Eloquent\Repositories;

use App\Application\Notes\Contracts\NoteCommandRepository;
use App\Domain\Common\ValueObjects\UserId;
use App\Domain\Notes\Entities\Note as DomainNote;
use App\Domain\Notes\Enums\NoteStatus as DomainNoteStatus;
use App\Domain\Notes\Enums\PublicationReasonType;
use App\Domain\Notes\ValueObjects\NoteId;
use App\Domain\Notes\ValueObjects\PublicationReason;
use App\Domain\Notes\ValueObjects\TagId;
use App\Persistence\Eloquent\Models\Note;
use DateTimeImmutable;

final class EloquentNoteCommandRepository implements NoteCommandRepository
{
    public function findOwnedById(UserId $userId, NoteId $noteId): ?DomainNote
    {
        $note = Note::query()
            ->with('tags')
            ->whereKey($noteId->value)
            ->where('user_id', $userId->value)
            ->first();

        if (!($note instanceof Note)) {
            return null;
        }

        return $this->mapToDomain($note);
    }

    public function save(DomainNote $domainNote): DomainNote
    {
        $eloquentNote = $domainNote->id() instanceof NoteId
            ? Note::query()->whereKey($domainNote->id()->value)->where('user_id', $domainNote->userId()->value)->firstOrFail()
            : new Note();

        $eloquentNote->fill([
            'user_id' => $domainNote->userId()->value,
            'title' => $domainNote->title(),
            'content' => $domainNote->content(),
            'status' => $domainNote->status()->value,
            'is_pinned' => $domainNote->isPinned(),
            'published_at' => $domainNote->publishedAt(),
            'publication_reason_type' => $domainNote->publicationReason()?->type()->value,
            'publication_reason_message' => $domainNote->publicationReason()?->message(),
        ]);
        $eloquentNote->save();
        $eloquentNote->tags()->sync(array_map(
            static fn (TagId $tagId): int => $tagId->value,
            $domainNote->tagIds(),
        ));

        return $this->mapToDomain($eloquentNote->load('tags'));
    }

    public function deleteOwnedById(UserId $userId, NoteId $noteId): bool
    {
        return Note::query()
            ->whereKey($noteId->value)
            ->where('user_id', $userId->value)
            ->delete() > 0;
    }

    private function mapToDomain(Note $note): DomainNote
    {
        $tagIds = [];

        foreach ($note->tags as $tag) {
            $tagIds[] = TagId::fromInt($tag->id);
        }

        return DomainNote::reconstitute(
            noteId: NoteId::fromInt($note->id),
            userId: UserId::fromInt($note->user_id),
            title: $note->title,
            content: $note->content,
            noteStatus: DomainNoteStatus::from($note->status->value),
            isPinned: $note->is_pinned,
            publishedAt: $note->published_at?->toAtomString() === null
                ? null
                : $this->toDateTimeImmutable($note->published_at->toAtomString()),
            publicationReason: $this->publicationReasonFromModel($note),
            tagIds: $tagIds,
        );
    }

    private function toDateTimeImmutable(string $value): DateTimeImmutable
    {
        return new DateTimeImmutable($value);
    }

    private function publicationReasonFromModel(Note $note): ?PublicationReason
    {
        return PublicationReason::fromNullable(
            $note->publication_reason_type === null
                ? null
                : PublicationReasonType::from($note->publication_reason_type),
            $note->publication_reason_message,
        );
    }
}
