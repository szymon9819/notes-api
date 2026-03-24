<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Persistence\Eloquent\Models\Note;
use App\Persistence\Eloquent\Models\Tag;
use App\Persistence\Eloquent\Models\User;

final class NoteShowEndpointTest extends FeatureTestCase
{
    public function test_show_returns_a_single_note_with_tags(): void
    {
        $user = $this->actingAsApiUser(User::factory()->create([
            'name' => 'Note Owner',
        ]));
        $tag = Tag::factory()->create([
            'name' => 'API',
            'slug' => 'api',
        ]);
        $note = Note::factory()->for($user)->create([
            'title' => 'Single note',
            'content' => 'Detailed content',
        ]);
        $note->tags()->attach($tag);

        $testResponse = $this->getJson(route('notes.show', $note));

        $testResponse
            ->assertOk()
            ->assertJsonPath('data.id', $note->id)
            ->assertJsonPath('data.title', 'Single note')
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.name', 'Note Owner')
            ->assertJsonPath('data.tags.0.slug', 'api');
    }

    public function test_show_returns_not_found_for_note_owned_by_other_user(): void
    {
        $this->actingAsApiUser();
        $note = Note::factory()->create();

        $this->getJson(route('notes.show', $note))
            ->assertNotFound();
    }

    public function test_show_returns_not_found_for_missing_note(): void
    {
        $this->actingAsApiUser();

        $this->getJson(route('notes.show', 999999))
            ->assertNotFound();
    }
}
