<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Note;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class NoteShowEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_returns_a_single_note_with_tags(): void
    {
        $tag = Tag::factory()->create([
            'name' => 'API',
            'slug' => 'api',
        ]);
        $note = Note::factory()->create([
            'title' => 'Single note',
            'content' => 'Detailed content',
        ]);
        $note->tags()->attach($tag);

        $testResponse = $this->getJson('/api/notes/' . $note->id);

        $testResponse
            ->assertOk()
            ->assertJsonPath('data.id', $note->id)
            ->assertJsonPath('data.title', 'Single note')
            ->assertJsonPath('data.tags.0.slug', 'api');
    }

    public function test_show_returns_not_found_for_missing_note(): void
    {
        $this->getJson('/api/notes/999999')
            ->assertNotFound();
    }
}
