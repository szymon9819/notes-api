<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Notes\Enums\NoteStatus;
use App\Domain\Notes\Enums\PublicationReasonType;
use App\Domain\Notes\ValueObjects\PublicationReason;
use App\Persistence\Eloquent\Models\Note;
use App\Persistence\Eloquent\Models\Tag;
use App\Persistence\Eloquent\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoNotesSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $publicationReason = new PublicationReason(
            PublicationReasonType::Announcement,
            'Share the released API with the team.',
        );

        /** @var array<string, int> $userIdsByEmail */
        $userIdsByEmail = collect([
            ['name' => 'Alice Demo', 'email' => 'alice@example.com'],
            ['name' => 'Bob Demo', 'email' => 'bob@example.com'],
        ])->mapWithKeys(
            fn (array $attributes): array => [
                $attributes['email'] => User::query()->firstOrCreate(
                    ['email' => $attributes['email']],
                    [
                        ...$attributes,
                        'password' => Hash::make('password'),
                    ],
                )->id,
            ],
        )->all();

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
                    'user_id' => $userIdsByEmail['alice@example.com'],
                    'title' => 'Ship the demo Notes API',
                    'content' => 'Expose a small public API with notes, tags and generated OpenAPI documentation.',
                    'status' => NoteStatus::Published,
                    'is_pinned' => true,
                    'published_at' => now()->subDay(),
                    'publication_reason_type' => $publicationReason->type()->value,
                    'publication_reason_message' => $publicationReason->message(),
                ],
                'tags' => ['work', 'laravel'],
            ],
            [
                'attributes' => [
                    'user_id' => $userIdsByEmail['alice@example.com'],
                    'title' => 'Collect product ideas',
                    'content' => 'Keep a short backlog of experiments for the next iteration.',
                    'status' => NoteStatus::Draft,
                    'is_pinned' => false,
                    'published_at' => null,
                ],
                'tags' => ['ideas'],
            ],
            [
                'attributes' => [
                    'user_id' => $userIdsByEmail['bob@example.com'],
                    'title' => 'Weekend reading list',
                    'content' => 'APIs, DX improvements and a couple of articles to revisit later.',
                    'status' => NoteStatus::Archived,
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
