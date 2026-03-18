<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Note;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TagsEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_tags_endpoint_returns_available_tags(): void
    {
        $tag = Tag::factory()->create([
            'name' => 'Backend',
            'slug' => 'backend',
        ]);

        Note::factory()->create()->tags()->attach($tag);

        $testResponse = $this->getJson('/api/tags');

        $testResponse
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'Backend',
                'slug' => 'backend',
                'notes_count' => 1,
            ]);
    }

    public function test_tags_endpoint_returns_tags_in_name_order(): void
    {
        Tag::factory()->create([
            'name' => 'Zulu',
            'slug' => 'zulu',
        ]);
        Tag::factory()->create([
            'name' => 'Alpha',
            'slug' => 'alpha',
        ]);

        $testResponse = $this->getJson('/api/tags');

        $testResponse
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Alpha')
            ->assertJsonPath('data.1.name', 'Zulu');
    }
}
