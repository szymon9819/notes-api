<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\NoteStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ListNotesRequest;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Http\Resources\NoteResource;
use App\Models\Note;
use App\Models\User;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

#[Group('Notes', 'Public demo endpoints for browsing and managing notes.', 10)]
class NoteController extends Controller
{
    #[Endpoint('listNotes', 'List notes', 'Returns a paginated list of notes with optional filters.')]
    public function index(ListNotesRequest $listNotesRequest): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $listNotesRequest->user();
        $perPage = $listNotesRequest->perPage();

        $lengthAwarePaginator = $user->notes()
            ->with(['tags', 'user'])
            ->when(
                $listNotesRequest->hasSearchTerm(),
                fn (Builder $query): Builder => $query->where(function (Builder $builder) use ($listNotesRequest): void {
                    $builder
                        ->where('title', 'like', '%' . $listNotesRequest->searchTerm() . '%')
                        ->orWhere('content', 'like', '%' . $listNotesRequest->searchTerm() . '%');
                }),
            )
            ->when(
                $listNotesRequest->hasStatusFilter(),
                fn (Builder $builder): Builder => $builder->where('status', $listNotesRequest->statusFilter()->value),
            )
            ->when(
                $listNotesRequest->hasTagFilter(),
                fn (Builder $builder): Builder => $builder->whereRelation('tags', 'slug', $listNotesRequest->tagFilter()),
            )
            ->when(
                $listNotesRequest->hasPinnedFilter(),
                fn (Builder $builder): Builder => $builder->where('is_pinned', $listNotesRequest->pinnedFilter()),
            )
            ->latest('published_at')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return NoteResource::collection($lengthAwarePaginator);
    }

    #[Endpoint('createNote', 'Create note', 'Creates a new demo note and optionally assigns tags.')]
    public function store(StoreNoteRequest $storeNoteRequest): JsonResponse
    {
        /** @var User $user */
        $user = $storeNoteRequest->user();
        $attributes = [
            'title' => $storeNoteRequest->title(),
            'status' => $storeNoteRequest->status(),
        ];

        if ($storeNoteRequest->hasContent()) {
            $attributes['content'] = $storeNoteRequest->contentIsNull()
                ? null
                : $storeNoteRequest->content();
        }

        if ($storeNoteRequest->hasPinnedFlag()) {
            $attributes['is_pinned'] = $storeNoteRequest->isPinned();
        }

        if ($storeNoteRequest->hasPublishedAt()) {
            $attributes['published_at'] = $storeNoteRequest->publishedAtIsNull()
                ? null
                : $storeNoteRequest->publishedAt();
        }

        $tagIds = $storeNoteRequest->tagIds();

        if ($attributes['status'] === NoteStatus::Published && blank($attributes['published_at'] ?? null)) {
            $attributes['published_at'] = now();
        }

        $note = $user->notes()->create($attributes);
        $note->tags()->sync($tagIds);

        return NoteResource::make($note->load(['tags', 'user']))
            ->response()
            ->setStatusCode(ResponseAlias::HTTP_CREATED);
    }

    #[Endpoint('showNote', 'Show note', 'Returns the details for a single note.')]
    public function show(Request $request, Note $note): NoteResource
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->id === $note->user_id, ResponseAlias::HTTP_NOT_FOUND);

        return NoteResource::make($note->load(['tags', 'user']));
    }

    #[Endpoint('updateNote', 'Update note', 'Updates an existing note and syncs its tags.', 'PATCH')]
    public function update(UpdateNoteRequest $updateNoteRequest, Note $note): NoteResource
    {
        /** @var User $user */
        $user = $updateNoteRequest->user();

        abort_unless($user->id === $note->user_id, ResponseAlias::HTTP_NOT_FOUND);

        $attributes = [];

        if ($updateNoteRequest->hasTitle()) {
            $attributes['title'] = $updateNoteRequest->title();
        }

        if ($updateNoteRequest->hasContent()) {
            $attributes['content'] = $updateNoteRequest->contentIsNull()
                ? null
                : $updateNoteRequest->content();
        }

        if ($updateNoteRequest->hasStatus()) {
            $attributes['status'] = $updateNoteRequest->status();
        }

        if ($updateNoteRequest->hasPinnedFlag()) {
            $attributes['is_pinned'] = $updateNoteRequest->isPinned();
        }

        if ($updateNoteRequest->hasPublishedAt()) {
            $attributes['published_at'] = $updateNoteRequest->publishedAtIsNull()
                ? null
                : $updateNoteRequest->publishedAt();
        }

        if (($attributes['status'] ?? null) === NoteStatus::Published && blank($attributes['published_at'] ?? null)) {
            $attributes['published_at'] = now();
        }

        $note->update($attributes);

        if ($updateNoteRequest->hasTagIds()) {
            $note->tags()->sync($updateNoteRequest->tagIds());
        }

        return NoteResource::make($note->load(['tags', 'user']));
    }

    #[Endpoint('deleteNote', 'Delete note', 'Deletes the selected note.')]
    public function destroy(Request $request, Note $note): Response
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->id === $note->user_id, ResponseAlias::HTTP_NOT_FOUND);

        $note->delete();

        return response()->noContent();
    }
}
