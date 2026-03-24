<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Notes\Enums\NoteStatus;
use App\Persistence\Eloquent\Models\Note;
use Database\Seeders\DemoNotesSeeder;

final class DemoNotesSeederTest extends FeatureTestCase
{
    public function test_demo_seeder_adds_publication_reason_to_published_notes(): void
    {
        $this->seed(DemoNotesSeeder::class);

        $this->assertDatabaseHas('notes', [
            'title' => 'Ship the demo Notes API',
            'status' => NoteStatus::Published->value,
            'publication_reason_type' => 'announcement',
            'publication_reason_message' => 'Share the released API with the team.',
        ]);

        $publishedNotes = Note::query()
            ->where('status', NoteStatus::Published->value)
            ->get();

        $this->assertGreaterThan(0, $publishedNotes->count());

        foreach ($publishedNotes as $publishedNote) {
            $this->assertNotNull($publishedNote->publication_reason_type);
            $this->assertNotNull($publishedNote->publication_reason_message);
        }
    }
}
