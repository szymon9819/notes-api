<?php

declare(strict_types=1);

namespace Tests\Unit\Unit\Domain\Notes;

use App\Domain\Common\ValueObjects\UserId;
use App\Domain\Notes\Entities\Note;
use App\Domain\Notes\Enums\NoteStatus;
use App\Domain\Notes\ValueObjects\NoteId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class NoteTest extends TestCase
{
    public function test_ensure_published_at_sets_now_for_a_published_note_without_a_timestamp(): void
    {
        $note = Note::create(
            userId: UserId::fromInt(1),
            title: 'Incident report',
            content: 'Service restored.',
            noteStatus: NoteStatus::Published,
            isPinned: false,
            publishedAt: null,
            publicationReason: null,
        );
        $now = new DateTimeImmutable('2026-03-24T10:00:00+00:00');

        $note->ensurePublishedAt($now);

        $this->assertEquals($now, $note->publishedAt());
    }

    public function test_reconstitute_creates_a_note_with_an_identifier(): void
    {
        $note = Note::reconstitute(
            noteId: NoteId::fromInt(10),
            userId: UserId::fromInt(1),
            title: 'Incident report',
            content: 'Service restored.',
            noteStatus: NoteStatus::Draft,
            isPinned: false,
            publishedAt: null,
            publicationReason: null,
            tagIds: [],
        );

        $this->assertSame(10, $note->id()?->value);
    }
}
