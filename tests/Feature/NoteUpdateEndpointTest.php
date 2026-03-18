<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Note;
use App\Models\Tag;

final class NoteUpdateEndpointTest extends FeatureTestCase
{
    public function test_update_changes_a_note_and_syncs_tags(): void
    {
        $user = $this->actingAsApiUser();
        $note = Note::factory()->for($user)->draft()->create([
            'title' => 'Old title',
        ]);
        $oldTag = Tag::factory()->create();
        $newTag = Tag::factory()->create();

        $note->tags()->attach($oldTag);

        $testResponse = $this->patchJson(route('notes.update', $note), [
            'title' => 'Updated title',
            'status' => 'published',
            'tag_ids' => [$newTag->id],
        ]);

        $testResponse
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.title', 'Updated title')
            ->assertJsonPath('data.status', 'published')
            ->assertJsonPath('data.tags.0.id', $newTag->id);

        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'user_id' => $user->id,
            'title' => 'Updated title',
            'status' => 'published',
        ]);
        $this->assertDatabaseMissing('note_tag', [
            'note_id' => $note->id,
            'tag_id' => $oldTag->id,
        ]);
    }

    public function test_update_preserves_tags_when_tag_ids_are_not_provided(): void
    {
        $user = $this->actingAsApiUser();
        $note = Note::factory()->for($user)->create();
        $tag = Tag::factory()->create();
        $note->tags()->attach($tag);

        $testResponse = $this->patchJson(route('notes.update', $note), [
            'title' => 'Renamed note',
        ]);

        $testResponse
            ->assertOk()
            ->assertJsonPath('data.title', 'Renamed note')
            ->assertJsonPath('data.tags.0.id', $tag->id);

        $this->assertDatabaseHas('note_tag', [
            'note_id' => $note->id,
            'tag_id' => $tag->id,
        ]);
    }

    public function test_update_can_clear_tags_and_published_at(): void
    {
        $user = $this->actingAsApiUser();
        $note = Note::factory()->for($user)->create([
            'status' => 'published',
        ]);
        $tag = Tag::factory()->create();
        $note->tags()->attach($tag);

        $testResponse = $this->patchJson(route('notes.update', $note), [
            'status' => 'draft',
            'published_at' => null,
            'tag_ids' => [],
        ]);

        $testResponse
            ->assertOk()
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.published_at', null)
            ->assertJsonCount(0, 'data.tags');

        $this->assertDatabaseMissing('note_tag', [
            'note_id' => $note->id,
            'tag_id' => $tag->id,
        ]);
    }

    public function test_update_validates_the_payload(): void
    {
        $user = $this->actingAsApiUser();
        $note = Note::factory()->for($user)->create();

        $testResponse = $this->patchJson(route('notes.update', $note), [
            'status' => 'wrong',
            'tag_ids' => [123456],
        ]);

        $testResponse
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status', 'tag_ids.0']);
    }

    public function test_update_returns_not_found_for_note_owned_by_other_user(): void
    {
        $this->actingAsApiUser();
        $note = Note::factory()->create();

        $this->patchJson(route('notes.update', $note), [
            'title' => 'Should not update',
        ])->assertNotFound();
    }
}
