<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Persistence\Eloquent\Models\Note;
use App\Persistence\Eloquent\Models\Tag;

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

        $testResponse = $this->putJson(route('notes.update', $note), $this->payloadFor($note, [
            'title' => 'Updated title',
            'status' => 'published',
            'publication_reason_type' => 'decision',
            'publication_reason_message' => 'Publish the approved revision.',
            'tag_ids' => [$newTag->id],
        ]));

        $testResponse
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.title', 'Updated title')
            ->assertJsonPath('data.status', 'published')
            ->assertJsonPath('data.publication_reason_type', 'decision')
            ->assertJsonPath('data.tags.0.id', $newTag->id)
            ->assertJsonCount(1, 'data.tags');
    }

    public function test_update_preserves_tags_when_tag_ids_are_not_provided(): void
    {
        $user = $this->actingAsApiUser();
        $note = Note::factory()->for($user)->create();
        $tag = Tag::factory()->create();
        $note->tags()->attach($tag);

        $testResponse = $this->putJson(route('notes.update', $note), $this->payloadFor($note, [
            'title' => 'Renamed note',
        ]));

        $testResponse
            ->assertOk()
            ->assertJsonPath('data.title', 'Renamed note')
            ->assertJsonPath('data.tags.0.id', $tag->id);
    }

    public function test_update_can_clear_tags_and_published_at(): void
    {
        $user = $this->actingAsApiUser();
        $note = Note::factory()->for($user)->create([
            'status' => 'published',
        ]);
        $tag = Tag::factory()->create();
        $note->tags()->attach($tag);

        $testResponse = $this->putJson(route('notes.update', $note), $this->payloadFor($note, [
            'status' => 'draft',
            'published_at' => null,
            'publication_reason_type' => null,
            'publication_reason_message' => null,
            'tag_ids' => [],
        ]));

        $testResponse
            ->assertOk()
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.published_at', null)
            ->assertJsonCount(0, 'data.tags');
    }

    public function test_update_validates_the_payload(): void
    {
        $user = $this->actingAsApiUser();
        $note = Note::factory()->for($user)->create();

        $testResponse = $this->putJson(route('notes.update', $note), $this->payloadFor($note, [
            'status' => 'wrong',
            'tag_ids' => [123456],
        ]));

        $testResponse
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status', 'tag_ids.0']);
    }

    public function test_update_returns_not_found_for_note_owned_by_other_user(): void
    {
        $this->actingAsApiUser();
        $note = Note::factory()->create();

        $this->putJson(route('notes.update', $note), $this->payloadFor($note, [
            'title' => 'Should not update',
        ]))->assertNotFound();
    }

    public function test_update_requires_publication_reason_when_publishing_a_note(): void
    {
        $user = $this->actingAsApiUser();
        $note = Note::factory()->for($user)->draft()->create();

        $testResponse = $this->putJson(route('notes.update', $note), $this->payloadFor($note, [
            'status' => 'published',
            'publication_reason_type' => null,
            'publication_reason_message' => null,
        ]));

        $testResponse
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['publication_reason_type', 'publication_reason_message']);
    }

    public function test_update_rejects_publication_reason_matching_note_title(): void
    {
        $user = $this->actingAsApiUser();
        $note = Note::factory()->for($user)->create([
            'title' => 'Engineering update',
            'publication_reason_type' => 'knowledge',
            'publication_reason_message' => 'Release notes',
        ]);

        $testResponse = $this->putJson(route('notes.update', $note), $this->payloadFor($note, [
            'title' => 'Release notes',
        ]));

        $testResponse
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['publication_reason_message']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payloadFor(Note $note, array $overrides = []): array
    {
        /** @var array<string, mixed> $payload */
        $payload = [
            'title' => $note->title,
            'content' => $note->content,
            'status' => $note->status->value,
            'is_pinned' => $note->is_pinned,
            'published_at' => $note->published_at?->toAtomString(),
            'publication_reason_type' => $note->publication_reason_type,
            'publication_reason_message' => $note->publication_reason_message,
            'tag_ids' => $note->tags()->pluck('tags.id')->all(),
        ];

        return array_replace($payload, $overrides);
    }
}
