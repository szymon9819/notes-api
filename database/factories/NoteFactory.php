<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\NoteStatus;
use App\Models\Note;
use App\Models\User;
use DateTime;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Note>
 */
class NoteFactory extends Factory
{
    /**
     * @return array{
     *     user_id: UserFactory,
     *     title: string,
     *     content: string,
     *     status: NoteStatus,
     *     is_pinned: bool,
     *     published_at: DateTime
     * }
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'content' => fake()->paragraph(),
            'status' => NoteStatus::Published,
            'is_pinned' => false,
            'published_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function draft(): static
    {
        /** @var array{status: NoteStatus, published_at: null} $state */
        $state = [
            'status' => NoteStatus::Draft,
            'published_at' => null,
        ];

        return $this->state(fn (): array => [
            ...$state,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (): array => [
            'status' => NoteStatus::Archived,
        ]);
    }

    public function pinned(): static
    {
        return $this->state(fn (): array => [
            'is_pinned' => true,
        ]);
    }
}
