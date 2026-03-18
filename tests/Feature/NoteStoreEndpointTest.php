<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Note;
use App\Models\Tag;

final class NoteStoreEndpointTest extends FeatureTestCase
{
    public function test_store_creates_a_note_with_tags(): void
    {
        $user = $this->actingAsApiUser();
        $firstTag = Tag::factory()->create();
        $secondTag = Tag::factory()->create();

        $testResponse = $this->postJson(route('notes.store'), [
            'title' => 'First demo note',
            'content' => 'Created from a feature test.',
            'status' => 'published',
            'is_pinned' => true,
            'tag_ids' => [$firstTag->id, $secondTag->id],
        ]);

        $testResponse
            ->assertCreated()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.title', 'First demo note')
            ->assertJsonPath('data.is_pinned', true)
            ->assertJsonCount(2, 'data.tags');

        $this->assertDatabaseHas('notes', [
            'user_id' => $user->id,
            'title' => 'First demo note',
            'status' => 'published',
            'is_pinned' => true,
        ]);
        $this->assertDatabaseHas('note_tag', [
            'note_id' => 1,
            'tag_id' => $firstTag->id,
        ]);
    }

    public function test_store_validates_the_payload(): void
    {
        $this->actingAsApiUser();

        $testResponse = $this->postJson(route('notes.store'), [
            'status' => 'invalid',
            'tag_ids' => [999],
        ]);

        $testResponse
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'status', 'tag_ids.0']);
    }

    public function test_store_sets_published_at_when_published_note_has_no_date(): void
    {
        $this->actingAsApiUser();

        $testResponse = $this->postJson(route('notes.store'), [
            'title' => 'Published without timestamp',
            'status' => 'published',
        ]);

        $testResponse
            ->assertCreated()
            ->assertJsonPath('data.status', 'published');

        $this->assertNotNull(Note::query()->firstOrFail()->published_at);
    }
}
