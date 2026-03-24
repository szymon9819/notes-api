<?php

declare(strict_types=1);

namespace App\Infrastructure\Presentation\Api;

use App\Application\Notes\DTO\NoteData;
use App\Application\Notes\DTO\PaginatedData;
use App\Application\Notes\DTO\TagData;

final class NoteDataPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function present(NoteData $noteData): array
    {
        return [
            'id' => $noteData->id,
            'user_id' => $noteData->userId,
            'title' => $noteData->title,
            'content' => $noteData->content,
            'status' => $noteData->status,
            'is_pinned' => $noteData->isPinned,
            'published_at' => $noteData->publishedAt,
            'publication_reason_type' => $noteData->publicationReasonType,
            'publication_reason_message' => $noteData->publicationReasonMessage,
            'created_at' => $noteData->createdAt,
            'updated_at' => $noteData->updatedAt,
            'user' => [
                'id' => $noteData->user->id,
                'name' => $noteData->user->name,
                'email' => $noteData->user->email,
            ],
            'tags' => array_map(
                static fn (TagData $tagData): array => [
                    'id' => $tagData->id,
                    'name' => $tagData->name,
                    'slug' => $tagData->slug,
                ],
                $noteData->tags,
            ),
        ];
    }

    /**
     * @param  PaginatedData<NoteData>  $paginatedData
     * @return array{data: list<array<string, mixed>>, links: array<string, string|null>, meta: array<string, mixed>}
     */
    public function presentPaginated(PaginatedData $paginatedData): array
    {
        $items = [];

        foreach ($paginatedData->items as $noteData) {
            $items[] = $this->present($noteData);
        }

        return [
            'data' => $items,
            'links' => $paginatedData->links,
            'meta' => $paginatedData->meta,
        ];
    }
}
