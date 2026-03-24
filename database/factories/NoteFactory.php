<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Notes\Enums\NoteStatus;
use App\Persistence\Eloquent\Models\Note;
use App\Persistence\Eloquent\Models\User;
use DateTime;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Note>
 */
class NoteFactory extends Factory
{
    protected $model = Note::class;

    /**
     * @return array{
     *     user_id: UserFactory,
     *     title: string,
     *     content: string,
     *     status: NoteStatus,
     *     is_pinned: bool,
     *     published_at: DateTime,
     *     publication_reason_type: string,
     *     publication_reason_message: string
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
            'publication_reason_type' => 'knowledge',
            'publication_reason_message' => fake()->sentence(3),
        ];
    }

    public function draft(): static
    {
        /** @var array{status: NoteStatus, published_at: null, publication_reason_type: null, publication_reason_message: null} $state */
        $state = [
            'status' => NoteStatus::Draft,
            'published_at' => null,
            'publication_reason_type' => null,
            'publication_reason_message' => null,
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
