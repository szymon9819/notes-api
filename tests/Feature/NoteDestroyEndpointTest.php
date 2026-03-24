<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Persistence\Eloquent\Models\Note;

final class NoteDestroyEndpointTest extends FeatureTestCase
{
    public function test_destroy_removes_a_note(): void
    {
        $user = $this->actingAsApiUser();
        $note = Note::factory()->for($user)->create();

        $this->deleteJson(route('notes.destroy', $note))
            ->assertNoContent();

        $this->assertDatabaseMissing('notes', [
            'id' => $note->id,
        ]);
    }

    public function test_destroy_returns_not_found_for_note_owned_by_other_user(): void
    {
        $this->actingAsApiUser();
        $note = Note::factory()->create();

        $this->deleteJson(route('notes.destroy', $note))
            ->assertNotFound();
    }
}
