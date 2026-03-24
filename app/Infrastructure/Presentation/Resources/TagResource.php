<?php

declare(strict_types=1);

namespace App\Infrastructure\Presentation\Resources;

use App\Persistence\Eloquent\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/** @mixin Tag */
class TagResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        /** @var Tag $tag */
        $tag = $this->resource;

        return [
            'id' => $tag->id,
            'name' => $tag->name,
            'slug' => $tag->slug,
            'notes_count' => $this->whenCounted('notes'),
        ];
    }
}
