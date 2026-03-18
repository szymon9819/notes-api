<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class NoteDestroyEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_destroy_removes_a_note(): void
    {
        $note = Note::factory()->create();

        $this->deleteJson('/api/notes/' . $note->id)
            ->assertNoContent();

        $this->assertDatabaseMissing('notes', [
            'id' => $note->id,
        ]);
    }
}
