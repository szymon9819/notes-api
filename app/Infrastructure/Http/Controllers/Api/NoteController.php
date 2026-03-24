<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\Api;

use App\Application\Common\CQRS\CommandBus;
use App\Application\Common\CQRS\QueryBus;
use App\Application\Notes\Commands\CreateNote\CreateNoteCommand;
use App\Application\Notes\Commands\DeleteNote\DeleteNoteCommand;
use App\Application\Notes\Commands\UpdateNote\UpdateNoteCommand;
use App\Application\Notes\DTO\NoteData;
use App\Application\Notes\DTO\PaginatedData;
use App\Application\Notes\Exceptions\NoteNotFound;
use App\Application\Notes\Queries\ListNotes\ListNotesQuery;
use App\Application\Notes\Queries\ShowNote\ShowNoteQuery;
use App\Domain\Common\ValueObjects\UserId;
use App\Domain\Notes\Enums\NoteStatus as DomainNoteStatus;
use App\Domain\Notes\Exceptions\InvalidPublicationReasonMessage;
use App\Domain\Notes\Exceptions\PublicationReasonCannotMatchTitle;
use App\Domain\Notes\Exceptions\PublicationReasonRequired;
use App\Domain\Notes\ValueObjects\NoteId;
use App\Infrastructure\Http\Controllers\Controller;
use App\Infrastructure\Http\Requests\ListNotesRequest;
use App\Infrastructure\Http\Requests\StoreNoteRequest;
use App\Infrastructure\Http\Requests\UpdateNoteRequest;
use App\Infrastructure\Presentation\Api\NoteDataPresenter;
use App\Persistence\Eloquent\Models\Note;
use App\Persistence\Eloquent\Models\User;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

#[Group('Notes', 'Public demo endpoints for browsing and managing notes.', 10)]
class NoteController extends Controller
{
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly CommandBus $commandBus,
        private readonly NoteDataPresenter $noteDataPresenter,
    ) {}

    #[Endpoint('listNotes', 'List notes', 'Returns a paginated list of notes with optional filters.')]
    public function index(ListNotesRequest $listNotesRequest): JsonResponse
    {
        /** @var User $user */
        $user = $listNotesRequest->user();

        $result = $this->queryBus->ask(new ListNotesQuery(
            userId: UserId::fromInt($user->id),
            perPage: $listNotesRequest->perPage(),
            search: $listNotesRequest->searchTerm(),
            status: $listNotesRequest->statusFilter()?->value,
            tag: $listNotesRequest->tagFilter(),
            pinned: $listNotesRequest->pinnedFilter(),
        ));

        if (!$result instanceof PaginatedData) {
            abort(500);
        }

        return response()->json($this->noteDataPresenter->presentPaginated($result));
    }

    #[Endpoint('createNote', 'Create note', 'Creates a new demo note and optionally assigns tags.')]
    public function store(StoreNoteRequest $storeNoteRequest): JsonResponse
    {
        /** @var User $user */
        $user = $storeNoteRequest->user();

        try {
            $note = $this->commandBus->dispatch(new CreateNoteCommand(
                userId: UserId::fromInt($user->id),
                title: $storeNoteRequest->title(),
                content: $storeNoteRequest->content(),
                status: DomainNoteStatus::from($storeNoteRequest->status()->value),
                isPinned: $storeNoteRequest->isPinned(),
                publishedAt: $storeNoteRequest->publishedAt(),
                publicationReasonType: $storeNoteRequest->publicationReasonType(),
                publicationReasonMessage: $storeNoteRequest->publicationReasonMessage(),
                tagIds: $storeNoteRequest->tagIds(),
            ));
        } catch (InvalidPublicationReasonMessage|PublicationReasonCannotMatchTitle|PublicationReasonRequired $exception) {
            throw $this->publicationReasonValidationException($exception);
        }

        if (!$note instanceof NoteData) {
            abort(500);
        }

        return response()->json([
            'data' => $this->noteDataPresenter->present($note),
        ], ResponseAlias::HTTP_CREATED);
    }

    #[Endpoint('showNote', 'Show note', 'Returns the details for a single note.')]
    public function show(Request $request, Note $note): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $result = $this->queryBus->ask(new ShowNoteQuery(
                userId: UserId::fromInt($user->id),
                noteId: NoteId::fromInt($note->id),
            ));
        } catch (NoteNotFound) {
            abort(ResponseAlias::HTTP_NOT_FOUND);
        }

        if (!$result instanceof NoteData) {
            abort(500);
        }

        return response()->json([
            'data' => $this->noteDataPresenter->present($result),
        ]);
    }

    #[Endpoint('updateNote', 'Update note', 'Replaces an existing note and syncs its tags.', 'PUT')]
    public function update(UpdateNoteRequest $updateNoteRequest, Note $note): JsonResponse
    {
        /** @var User $user */
        $user = $updateNoteRequest->user();

        try {
            $result = $this->commandBus->dispatch(new UpdateNoteCommand(
                userId: UserId::fromInt($user->id),
                noteId: NoteId::fromInt($note->id),
                content: $updateNoteRequest->content(),
                title: $updateNoteRequest->title(),
                status: DomainNoteStatus::from($updateNoteRequest->status()->value),
                isPinned: $updateNoteRequest->isPinned(),
                publishedAt: $updateNoteRequest->publishedAt(),
                publicationReasonType: $updateNoteRequest->publicationReasonType(),
                publicationReasonMessage: $updateNoteRequest->publicationReasonMessage(),
                tagIds: $updateNoteRequest->tagIds(),
            ));
        } catch (NoteNotFound) {
            abort(ResponseAlias::HTTP_NOT_FOUND);
        } catch (InvalidPublicationReasonMessage|PublicationReasonCannotMatchTitle|PublicationReasonRequired $exception) {
            throw $this->publicationReasonValidationException($exception);
        }

        if (!$result instanceof NoteData) {
            abort(500);
        }

        return response()->json([
            'data' => $this->noteDataPresenter->present($result),
        ]);
    }

    #[Endpoint('deleteNote', 'Delete note', 'Deletes the selected note.')]
    public function destroy(Request $request, Note $note): Response
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $this->commandBus->dispatch(new DeleteNoteCommand(
                userId: UserId::fromInt($user->id),
                noteId: NoteId::fromInt($note->id),
            ));
        } catch (NoteNotFound) {
            abort(ResponseAlias::HTTP_NOT_FOUND);
        }

        return response()->noContent();
    }

    private function publicationReasonValidationException(
        InvalidPublicationReasonMessage|PublicationReasonCannotMatchTitle|PublicationReasonRequired $exception,
    ): ValidationException {
        if ($exception instanceof PublicationReasonRequired) {
            return ValidationException::withMessages([
                'publication_reason_type' => [$exception->getMessage()],
                'publication_reason_message' => [$exception->getMessage()],
            ]);
        }

        return ValidationException::withMessages([
            'publication_reason_message' => [$exception->getMessage()],
        ]);
    }
}
