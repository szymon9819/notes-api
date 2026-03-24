<?php

declare(strict_types=1);

namespace App\Infrastructure\Presentation\Api;

use App\Application\Notes\DTO\TagData;

final class TagDataPresenter
{
    /**
     * @param  list<TagData>  $tags
     * @return array{data: list<array<string, mixed>>}
     */
    public function presentList(array $tags): array
    {
        return [
            'data' => array_map(
                static fn (TagData $tagData): array => [
                    'id' => $tagData->id,
                    'name' => $tagData->name,
                    'slug' => $tagData->slug,
                    'notes_count' => $tagData->notesCount,
                ],
                $tags,
            ),
        ];
    }
}
