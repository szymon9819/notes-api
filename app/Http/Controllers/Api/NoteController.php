<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListNotesRequest;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Http\Resources\NoteResource;
use App\Models\Note;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

#[Group('Notes', 'Public demo endpoints for browsing and managing notes.', 10)]
class NoteController extends Controller
{
    #[Endpoint('listNotes', 'List notes', 'Returns a paginated list of notes with optional filters.')]
    public function index(ListNotesRequest $listNotesRequest): AnonymousResourceCollection
    {
        $filters = $this->validatedFilters($listNotesRequest);
        $searchTerm = $filters['search'] ?? null;
        $status = $filters['status'] ?? null;
        $tag = $filters['tag'] ?? null;
        $pinned = $filters['pinned'] ?? null;
        $perPage = $filters['per_page'] ?? 10;

        $lengthAwarePaginator = Note::query()
            ->with('tags')
            ->when(
                $searchTerm !== null,
                fn (Builder $query): Builder => $query->where(function (Builder $builder) use ($searchTerm): void {
                    $builder
                        ->where('title', 'like', '%' . $searchTerm . '%')
                        ->orWhere('content', 'like', '%' . $searchTerm . '%');
                }),
            )
            ->when(
                $status !== null,
                fn (Builder $builder): Builder => $builder->where('status', $status),
            )
            ->when(
                $tag !== null,
                fn (Builder $builder): Builder => $builder->whereRelation('tags', 'slug', $tag),
            )
            ->when(
                $pinned !== null,
                fn (Builder $builder): Builder => $builder->where('is_pinned', $pinned),
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
        $attributes = $this->validatedStoreAttributes($storeNoteRequest);
        $tagIds = $this->validatedTagIds($storeNoteRequest);

        if ($attributes['status'] === 'published' && blank($attributes['published_at'] ?? null)) {
            $attributes['published_at'] = now();
        }

        $note = Note::query()->create($attributes);
        $note->tags()->sync($tagIds);

        return NoteResource::make($note->load('tags'))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    #[Endpoint('showNote', 'Show note', 'Returns the details for a single note.')]
    public function show(Note $note): NoteResource
    {
        return NoteResource::make($note->load('tags'));
    }

    #[Endpoint('updateNote', 'Update note', 'Updates an existing note and syncs its tags.', 'PATCH')]
    public function update(UpdateNoteRequest $updateNoteRequest, Note $note): NoteResource
    {
        $attributes = $this->validatedUpdateAttributes($updateNoteRequest);

        if (($attributes['status'] ?? null) === 'published' && blank($attributes['published_at'] ?? null)) {
            $attributes['published_at'] = now();
        }

        $note->update($attributes);

        if ($updateNoteRequest->has('tag_ids')) {
            $note->tags()->sync($this->validatedTagIds($updateNoteRequest));
        }

        return NoteResource::make($note->load('tags'));
    }

    #[Endpoint('deleteNote', 'Delete note', 'Deletes the selected note.')]
    public function destroy(Note $note): Response
    {
        $note->delete();

        return response()->noContent();
    }

    /**
     * @return array{
     *     search?: string,
     *     status?: 'draft'|'published'|'archived',
     *     tag?: string,
     *     pinned?: bool,
     *     per_page?: int
     * }
     */
    private function validatedFilters(ListNotesRequest $listNotesRequest): array
    {
        $validated = $listNotesRequest->validated();
        $filters = [];

        if (isset($validated['search'])) {
            $filters['search'] = $this->stringValue($validated['search']);
        }

        if (isset($validated['status'])) {
            $filters['status'] = $this->noteStatus($validated['status']);
        }

        if (isset($validated['tag'])) {
            $filters['tag'] = $this->stringValue($validated['tag']);
        }

        if (array_key_exists('pinned', $validated)) {
            $filters['pinned'] = $listNotesRequest->boolean('pinned');
        }

        if (isset($validated['per_page'])) {
            $filters['per_page'] = $this->intValue($validated['per_page']);
        }

        return $filters;
    }

    /**
     * @return array{
     *     title: string,
     *     content?: string|null,
     *     status: 'draft'|'published'|'archived',
     *     is_pinned?: bool,
     *     published_at?: Carbon|string|null
     * }
     */
    private function validatedStoreAttributes(StoreNoteRequest $storeNoteRequest): array
    {
        $validated = $storeNoteRequest->validated();

        $attributes = [
            'title' => $this->stringValue($validated['title']),
            'status' => $this->noteStatus($validated['status']),
        ];

        if (array_key_exists('content', $validated)) {
            $attributes['content'] = is_string($validated['content']) ? $validated['content'] : null;
        }

        if (array_key_exists('is_pinned', $validated)) {
            $attributes['is_pinned'] = $storeNoteRequest->boolean('is_pinned');
        }

        if (array_key_exists('published_at', $validated)) {
            $attributes['published_at'] = is_string($validated['published_at']) ? $validated['published_at'] : null;
        }

        return $attributes;
    }

    /**
     * @return array{
     *     title?: string,
     *     content?: string|null,
     *     status?: 'draft'|'published'|'archived',
     *     is_pinned?: bool,
     *     published_at?: Carbon|string|null
     * }
     */
    private function validatedUpdateAttributes(UpdateNoteRequest $updateNoteRequest): array
    {
        $validated = $updateNoteRequest->validated();
        $attributes = [];

        if (array_key_exists('title', $validated)) {
            $attributes['title'] = $this->stringValue($validated['title']);
        }

        if (array_key_exists('content', $validated)) {
            $attributes['content'] = is_string($validated['content']) ? $validated['content'] : null;
        }

        if (array_key_exists('status', $validated)) {
            $attributes['status'] = $this->noteStatus($validated['status']);
        }

        if (array_key_exists('is_pinned', $validated)) {
            $attributes['is_pinned'] = $updateNoteRequest->boolean('is_pinned');
        }

        if (array_key_exists('published_at', $validated)) {
            $attributes['published_at'] = is_string($validated['published_at']) ? $validated['published_at'] : null;
        }

        return $attributes;
    }

    /**
     * @return list<int>
     */
    private function validatedTagIds(StoreNoteRequest|UpdateNoteRequest $request): array
    {
        $validated = $request->validated();
        $tagIds = $validated['tag_ids'] ?? [];

        if (! is_array($tagIds)) {
            return [];
        }

        return array_values(array_map($this->intValue(...), $tagIds));
    }

    private function stringValue(mixed $value): string
    {
        assert(is_string($value));

        return $value;
    }

    private function intValue(mixed $value): int
    {
        assert(is_int($value) || (is_string($value) && is_numeric($value)));

        return (int) $value;
    }

    /**
     * @return 'draft'|'published'|'archived'
     */
    private function noteStatus(mixed $value): string
    {
        assert(is_string($value));
        assert(in_array($value, ['draft', 'published', 'archived'], true));

        return $value;
    }
}
