<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Note;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class NoteUpdateEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_changes_a_note_and_syncs_tags(): void
    {
        $note = Note::factory()->draft()->create([
            'title' => 'Old title',
        ]);
        $oldTag = Tag::factory()->create();
        $newTag = Tag::factory()->create();

        $note->tags()->attach($oldTag);

        $testResponse = $this->patchJson('/api/notes/' . $note->id, [
            'title' => 'Updated title',
            'status' => 'published',
            'tag_ids' => [$newTag->id],
        ]);

        $testResponse
            ->assertOk()
            ->assertJsonPath('data.title', 'Updated title')
            ->assertJsonPath('data.status', 'published')
            ->assertJsonPath('data.tags.0.id', $newTag->id);

        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
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
        $note = Note::factory()->create();
        $tag = Tag::factory()->create();
        $note->tags()->attach($tag);

        $testResponse = $this->patchJson('/api/notes/' . $note->id, [
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
        $note = Note::factory()->create([
            'status' => 'published',
        ]);
        $tag = Tag::factory()->create();
        $note->tags()->attach($tag);

        $testResponse = $this->patchJson('/api/notes/' . $note->id, [
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
        $note = Note::factory()->create();

        $testResponse = $this->patchJson('/api/notes/' . $note->id, [
            'status' => 'wrong',
            'tag_ids' => [123456],
        ]);

        $testResponse
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status', 'tag_ids.0']);
    }
}
