<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Note;
use DateTime;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Note>
 */
class NoteFactory extends Factory
{
    /**
     * @return array{
     *     title: string,
     *     content: string,
     *     status: string,
     *     is_pinned: bool,
     *     published_at: DateTime
     * }
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'content' => fake()->paragraph(),
            'status' => 'published',
            'is_pinned' => false,
            'published_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function draft(): static
    {
        /** @var array{status: string, published_at: null} $state */
        $state = [
            'status' => 'draft',
            'published_at' => null,
        ];

        return $this->state(fn (): array => [
            ...$state,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (): array => [
            'status' => 'archived',
        ]);
    }

    public function pinned(): static
    {
        return $this->state(fn (): array => [
            'is_pinned' => true,
        ]);
    }
}
