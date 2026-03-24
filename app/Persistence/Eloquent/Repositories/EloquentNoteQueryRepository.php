<?php

declare(strict_types=1);

namespace App\Persistence\Eloquent\Repositories;

use App\Application\Notes\Contracts\NoteQueryRepository;
use App\Application\Notes\DTO\NoteData;
use App\Application\Notes\DTO\PaginatedData;
use App\Application\Notes\DTO\TagData;
use App\Application\Notes\DTO\UserData;
use App\Application\Notes\Queries\ListNotes\ListNotesQuery;
use App\Domain\Common\ValueObjects\UserId;
use App\Domain\Notes\ValueObjects\NoteId;
use App\Persistence\Eloquent\Models\Note;
use App\Persistence\Eloquent\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use LogicException;

final class EloquentNoteQueryRepository implements NoteQueryRepository
{
    public function paginateOwnedBy(ListNotesQuery $listNotesQuery): PaginatedData
    {
        $lengthAwarePaginator = Note::query()
            ->with(['tags', 'user'])
            ->where('user_id', $listNotesQuery->userId->value)
            ->when(
                $listNotesQuery->search !== null,
                fn (Builder $builder): Builder => $builder->where(function (Builder $queryBuilder) use ($listNotesQuery): void {
                    $queryBuilder
                        ->where('title', 'like', '%' . $listNotesQuery->search . '%')
                        ->orWhere('content', 'like', '%' . $listNotesQuery->search . '%');
                }),
            )
            ->when(
                $listNotesQuery->status !== null,
                fn (Builder $builder): Builder => $builder->where('status', $listNotesQuery->status),
            )
            ->when(
                $listNotesQuery->tag !== null,
                fn (Builder $builder): Builder => $builder->whereRelation('tags', 'slug', $listNotesQuery->tag),
            )
            ->when(
                $listNotesQuery->pinned !== null,
                fn (Builder $builder): Builder => $builder->where('is_pinned', $listNotesQuery->pinned),
            )
            ->latest('published_at')
            ->latest('id')
            ->paginate($listNotesQuery->perPage)
            ->withQueryString();

        $items = [];

        foreach ($lengthAwarePaginator->items() as $item) {
            $items[] = $this->mapNote($item);
        }

        return new PaginatedData(
            items: $items,
            links: $this->paginationLinks($lengthAwarePaginator),
            meta: $this->paginationMeta($lengthAwarePaginator),
        );
    }

    public function findOwnedById(UserId $userId, NoteId $noteId): ?NoteData
    {
        $note = Note::query()
            ->with(['tags', 'user'])
            ->whereKey($noteId->value)
            ->where('user_id', $userId->value)
            ->first();

        if (!($note instanceof Note)) {
            return null;
        }

        return $this->mapNote($note);
    }

    private function mapNote(Note $note): NoteData
    {
        $user = $note->user;

        if (!$user instanceof User) {
            throw new LogicException('Expected note user relation to be loaded.');
        }

        $tags = [];

        foreach ($note->tags as $tag) {
            $tags[] = new TagData(
                id: $tag->id,
                name: $tag->name,
                slug: $tag->slug,
            );
        }

        return new NoteData(
            id: $note->id,
            userId: $note->user_id,
            title: $note->title,
            content: $note->content,
            status: $note->status->value,
            isPinned: $note->is_pinned,
            publishedAt: $note->published_at?->toAtomString(),
            publicationReasonType: $note->publication_reason_type,
            publicationReasonMessage: $note->publication_reason_message,
            createdAt: $note->created_at?->toAtomString(),
            updatedAt: $note->updated_at?->toAtomString(),
            user: new UserData(
                id: $user->id,
                name: $user->name,
                email: $user->email,
            ),
            tags: $tags,
        );
    }

    /**
     * @param  LengthAwarePaginator<int, Note>  $lengthAwarePaginator
     * @return array<string, string|null>
     */
    private function paginationLinks(LengthAwarePaginator $lengthAwarePaginator): array
    {
        return [
            'first' => $lengthAwarePaginator->url(1),
            'last' => $lengthAwarePaginator->url($lengthAwarePaginator->lastPage()),
            'prev' => $lengthAwarePaginator->previousPageUrl(),
            'next' => $lengthAwarePaginator->nextPageUrl(),
        ];
    }

    /**
     * @param  LengthAwarePaginator<int, Note>  $lengthAwarePaginator
     * @return array<string, mixed>
     */
    private function paginationMeta(LengthAwarePaginator $lengthAwarePaginator): array
    {
        return [
            'current_page' => $lengthAwarePaginator->currentPage(),
            'from' => $lengthAwarePaginator->firstItem(),
            'last_page' => $lengthAwarePaginator->lastPage(),
            'links' => $lengthAwarePaginator->linkCollection()->toArray(),
            'path' => $lengthAwarePaginator->path(),
            'per_page' => $lengthAwarePaginator->perPage(),
            'to' => $lengthAwarePaginator->lastItem(),
            'total' => $lengthAwarePaginator->total(),
        ];
    }
}
