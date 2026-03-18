<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Note;
use App\Models\Tag;

final class TagsEndpointTest extends FeatureTestCase
{
    public function test_tags_endpoint_returns_available_tags(): void
    {
        $user = $this->actingAsApiUser();
        $tag = Tag::factory()->create([
            'name' => 'Backend',
            'slug' => 'backend',
        ]);

        Note::factory()->for($user)->create()->tags()->attach($tag);

        $testResponse = $this->getJson(route('tags.index'));

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
        $user = $this->actingAsApiUser();
        $firstTag = Tag::factory()->create([
            'name' => 'Zulu',
            'slug' => 'zulu',
        ]);
        $secondTag = Tag::factory()->create([
            'name' => 'Alpha',
            'slug' => 'alpha',
        ]);

        Note::factory()->for($user)->create()->tags()->attach($firstTag);
        Note::factory()->for($user)->create()->tags()->attach($secondTag);

        $testResponse = $this->getJson(route('tags.index'));

        $testResponse
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Alpha')
            ->assertJsonPath('data.1.name', 'Zulu');
    }
}
