<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Persistence\Eloquent\Models\Tag;

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
            'publication_reason_type' => 'knowledge',
            'publication_reason_message' => 'Share reference notes with the team.',
            'tag_ids' => [$firstTag->id, $secondTag->id],
        ]);

        $testResponse
            ->assertCreated()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.title', 'First demo note')
            ->assertJsonPath('data.publication_reason_type', 'knowledge')
            ->assertJsonPath('data.publication_reason_message', 'Share reference notes with the team.')
            ->assertJsonPath('data.tags.0.id', $firstTag->id)
            ->assertJsonPath('data.tags.1.id', $secondTag->id);
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
            'publication_reason_type' => 'announcement',
            'publication_reason_message' => 'Notify the team about the release.',
        ]);

        $testResponse
            ->assertCreated()
            ->assertJsonPath('data.status', 'published');

        $this->assertIsString($testResponse->json('data.published_at'));
    }

    public function test_store_requires_publication_reason_for_published_note(): void
    {
        $this->actingAsApiUser();

        $testResponse = $this->postJson(route('notes.store'), [
            'title' => 'Published note without reason',
            'status' => 'published',
        ]);

        $testResponse
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['publication_reason_type', 'publication_reason_message']);
    }

    public function test_store_allows_null_publication_reason_for_non_published_note(): void
    {
        $this->actingAsApiUser();

        $testResponse = $this->postJson(route('notes.store'), [
            'title' => 'Draft note with explicit nulls',
            'status' => 'draft',
            'publication_reason_type' => null,
            'publication_reason_message' => null,
        ]);

        $testResponse
            ->assertCreated()
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.publication_reason_type', null)
            ->assertJsonPath('data.publication_reason_message', null);
    }

    public function test_store_rejects_publication_reason_with_url(): void
    {
        $this->actingAsApiUser();

        $testResponse = $this->postJson(route('notes.store'), [
            'title' => 'Published note',
            'status' => 'published',
            'publication_reason_type' => 'knowledge',
            'publication_reason_message' => 'Read https://example.com first.',
        ]);

        $testResponse
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['publication_reason_message']);
    }

    public function test_store_rejects_publication_reason_matching_title(): void
    {
        $this->actingAsApiUser();

        $testResponse = $this->postJson(route('notes.store'), [
            'title' => 'Quarterly roadmap',
            'status' => 'published',
            'publication_reason_type' => 'decision',
            'publication_reason_message' => 'quarterly roadmap',
        ]);

        $testResponse
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['publication_reason_message']);
    }
}
