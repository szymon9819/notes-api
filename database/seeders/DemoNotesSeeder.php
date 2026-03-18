<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Note;
use App\Models\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemoNotesSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        /** @var array<string, int> $tagIdsBySlug */
        $tagIdsBySlug = collect([
            ['name' => 'Work', 'slug' => 'work'],
            ['name' => 'Ideas', 'slug' => 'ideas'],
            ['name' => 'Personal', 'slug' => 'personal'],
            ['name' => 'Laravel', 'slug' => 'laravel'],
        ])->mapWithKeys(
            fn (array $attributes): array => [
                $attributes['slug'] => Tag::query()->firstOrCreate($attributes, $attributes)->id,
            ],
        )->all();

        $notes = [
            [
                'attributes' => [
                    'title' => 'Ship the demo Notes API',
                    'content' => 'Expose a small public API with notes, tags and generated OpenAPI documentation.',
                    'status' => 'published',
                    'is_pinned' => true,
                    'published_at' => now()->subDay(),
                ],
                'tags' => ['work', 'laravel'],
            ],
            [
                'attributes' => [
                    'title' => 'Collect product ideas',
                    'content' => 'Keep a short backlog of experiments for the next iteration.',
                    'status' => 'draft',
                    'is_pinned' => false,
                    'published_at' => null,
                ],
                'tags' => ['ideas'],
            ],
            [
                'attributes' => [
                    'title' => 'Weekend reading list',
                    'content' => 'APIs, DX improvements and a couple of articles to revisit later.',
                    'status' => 'archived',
                    'is_pinned' => false,
                    'published_at' => now()->subWeek(),
                ],
                'tags' => ['personal'],
            ],
        ];

        foreach ($notes as $noteDefinition) {
            $note = Note::query()->updateOrCreate(
                ['title' => $noteDefinition['attributes']['title']],
                $noteDefinition['attributes'],
            );

            $note->tags()->sync(
                array_map(
                    static fn (string $slug): int => $tagIdsBySlug[$slug],
                    $noteDefinition['tags'],
                ),
            );
        }
    }
}
