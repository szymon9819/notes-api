<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Note;
use App\Models\Tag;

final class NotesIndexEndpointTest extends FeatureTestCase
{
    public function test_index_returns_paginated_notes(): void
    {
        $user = $this->actingAsApiUser();
        $tag = Tag::factory()->create([
            'name' => 'Laravel',
            'slug' => 'laravel',
        ]);

        $note = Note::factory()->for($user)->create([
            'title' => 'Public API demo',
        ]);
        $note->tags()->attach($tag);

        $testResponse = $this->getJson(route('notes.index'));

        $testResponse
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Public API demo')
            ->assertJsonPath('data.0.user.id', $note->user_id)
            ->assertJsonPath('data.0.tags.0.slug', 'laravel')
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ]);
    }

    public function test_index_can_filter_notes_by_status(): void
    {
        $user = $this->actingAsApiUser();

        Note::factory()->for($user)->draft()->create([
            'title' => 'Draft note',
        ]);
        Note::factory()->for($user)->create([
            'title' => 'Published note',
        ]);

        $testResponse = $this->getJson(route('notes.index', ['status' => 'draft']));

        $testResponse
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Draft note');
    }

    public function test_index_can_filter_notes_by_tag_and_pinned_state(): void
    {
        $user = $this->actingAsApiUser();
        $laravelTag = Tag::factory()->create([
            'name' => 'Laravel',
            'slug' => 'laravel',
        ]);
        $phpTag = Tag::factory()->create([
            'name' => 'PHP',
            'slug' => 'php',
        ]);

        $matchingNote = Note::factory()->for($user)->pinned()->create([
            'title' => 'Pinned Laravel note',
        ]);
        $matchingNote->tags()->attach($laravelTag);

        $otherPinnedNote = Note::factory()->for($user)->pinned()->create([
            'title' => 'Pinned PHP note',
        ]);
        $otherPinnedNote->tags()->attach($phpTag);

        $unpinnedLaravelNote = Note::factory()->for($user)->create([
            'title' => 'Not pinned Laravel note',
            'is_pinned' => false,
        ]);
        $unpinnedLaravelNote->tags()->attach($laravelTag);

        $testResponse = $this->getJson(route('notes.index', ['tag' => 'laravel', 'pinned' => 1]));

        $testResponse
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Pinned Laravel note');
    }

    public function test_index_can_search_notes_and_honor_per_page(): void
    {
        $user = $this->actingAsApiUser();

        Note::factory()->for($user)->create([
            'title' => 'Release checklist',
            'content' => 'Demo launch tasks',
        ]);
        Note::factory()->for($user)->create([
            'title' => 'Second checklist',
            'content' => 'More demo launch tasks',
        ]);
        Note::factory()->for($user)->create([
            'title' => 'Unrelated note',
            'content' => 'Nothing to see here',
        ]);

        $testResponse = $this->getJson(route('notes.index', ['search' => 'launch', 'per_page' => 1]));

        $testResponse
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_index_validates_filters(): void
    {
        $this->actingAsApiUser();

        $testResponse = $this->getJson(route('notes.index', [
            'status' => 'broken',
            'per_page' => 100,
            'pinned' => 'maybe',
        ]));

        $testResponse
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status', 'per_page', 'pinned']);
    }
}
