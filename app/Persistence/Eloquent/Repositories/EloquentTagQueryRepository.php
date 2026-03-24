<?php

declare(strict_types=1);

namespace App\Persistence\Eloquent\Repositories;

use App\Application\Notes\Contracts\TagQueryRepository;
use App\Application\Notes\DTO\TagData;
use App\Domain\Common\ValueObjects\UserId;
use App\Persistence\Eloquent\Models\Tag;

final class EloquentTagQueryRepository implements TagQueryRepository
{
    public function allForUser(UserId $userId): array
    {
        $tags = Tag::query()
            ->select('tags.id', 'tags.name', 'tags.slug')
            ->selectRaw('COUNT(DISTINCT notes.id) as notes_count')
            ->join('note_tag', 'note_tag.tag_id', '=', 'tags.id')
            ->join('notes', 'notes.id', '=', 'note_tag.note_id')
            ->where('notes.user_id', $userId->value)
            ->groupBy('tags.id', 'tags.name', 'tags.slug')
            ->orderBy('tags.name')
            ->get()
            ->map(
                static fn (Tag $tag): TagData => new TagData(
                    id: $tag->id,
                    name: $tag->name,
                    slug: $tag->slug,
                    notesCount: $tag->notes_count,
                ),
            )
            ->values()
            ->all();

        return array_values($tags);
    }
}
