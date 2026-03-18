<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Note;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class NotesIndexEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_paginated_notes(): void
    {
        $tag = Tag::factory()->create([
            'name' => 'Laravel',
            'slug' => 'laravel',
        ]);

        $note = Note::factory()->create([
            'title' => 'Public API demo',
        ]);
        $note->tags()->attach($tag);

        $testResponse = $this->getJson('/api/notes');

        $testResponse
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Public API demo')
            ->assertJsonPath('data.0.tags.0.slug', 'laravel')
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ]);
    }

    public function test_index_can_filter_notes_by_status(): void
    {
        Note::factory()->draft()->create([
            'title' => 'Draft note',
        ]);
        Note::factory()->create([
            'title' => 'Published note',
        ]);

        $testResponse = $this->getJson('/api/notes?status=draft');

        $testResponse
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Draft note');
    }

    public function test_index_can_filter_notes_by_tag_and_pinned_state(): void
    {
        $laravelTag = Tag::factory()->create([
            'name' => 'Laravel',
            'slug' => 'laravel',
        ]);
        $phpTag = Tag::factory()->create([
            'name' => 'PHP',
            'slug' => 'php',
        ]);

        $matchingNote = Note::factory()->pinned()->create([
            'title' => 'Pinned Laravel note',
        ]);
        $matchingNote->tags()->attach($laravelTag);

        $otherPinnedNote = Note::factory()->pinned()->create([
            'title' => 'Pinned PHP note',
        ]);
        $otherPinnedNote->tags()->attach($phpTag);

        $unpinnedLaravelNote = Note::factory()->create([
            'title' => 'Not pinned Laravel note',
            'is_pinned' => false,
        ]);
        $unpinnedLaravelNote->tags()->attach($laravelTag);

        $testResponse = $this->getJson('/api/notes?tag=laravel&pinned=1');

        $testResponse
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Pinned Laravel note');
    }

    public function test_index_can_search_notes_and_honor_per_page(): void
    {
        Note::factory()->create([
            'title' => 'Release checklist',
            'content' => 'Demo launch tasks',
        ]);
        Note::factory()->create([
            'title' => 'Second checklist',
            'content' => 'More demo launch tasks',
        ]);
        Note::factory()->create([
            'title' => 'Unrelated note',
            'content' => 'Nothing to see here',
        ]);

        $testResponse = $this->getJson('/api/notes?search=launch&per_page=1');

        $testResponse
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_index_validates_filters(): void
    {
        $testResponse = $this->getJson('/api/notes?status=broken&per_page=100&pinned=maybe');

        $testResponse
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status', 'per_page', 'pinned']);
    }
}
