<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Persistence\Eloquent\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    /**
     * @return array{name: string, slug: string}
     */
    public function definition(): array
    {
        $name = (string) str(fake()->unique()->sentence(fake()->numberBetween(1, 2)))
            ->before('.')
            ->trim();

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
        ];
    }
}
