<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/** @mixin Note */
class NoteResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        /** @var Note $note */
        $note = $this->resource;

        return [
            'id' => $note->id,
            'title' => $note->title,
            'content' => $note->content,
            'status' => $note->status,
            'is_pinned' => $note->is_pinned,
            'published_at' => $note->published_at?->toAtomString(),
            'created_at' => $note->created_at?->toAtomString(),
            'updated_at' => $note->updated_at?->toAtomString(),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
        ];
    }
}
