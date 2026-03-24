<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Notes;

use App\Domain\Common\ValueObjects\UserId;
use App\Domain\Notes\Entities\Note;
use App\Domain\Notes\Enums\NoteStatus;
use App\Domain\Notes\Enums\PublicationReasonType;
use App\Domain\Notes\Exceptions\PublicationReasonCannotMatchTitle;
use App\Domain\Notes\Exceptions\PublicationReasonRequired;
use App\Domain\Notes\ValueObjects\PublicationReason;
use PHPUnit\Framework\TestCase;

final class NotePublicationRuleTest extends TestCase
{
    public function test_published_note_requires_a_publication_reason(): void
    {
        $note = Note::create(
            userId: UserId::fromInt(1),
            title: 'Product launch',
            content: 'Launch details.',
            noteStatus: NoteStatus::Published,
            isPinned: false,
            publishedAt: null,
            publicationReason: null,
        );

        $this->expectException(PublicationReasonRequired::class);

        $note->ensurePublicationRules();
    }

    public function test_published_note_rejects_a_reason_matching_the_title(): void
    {
        $note = Note::create(
            userId: UserId::fromInt(1),
            title: 'Quarterly roadmap',
            content: 'Roadmap details.',
            noteStatus: NoteStatus::Published,
            isPinned: false,
            publishedAt: null,
            publicationReason: new PublicationReason(
                PublicationReasonType::Decision,
                'quarterly roadmap',
            ),
        );

        $this->expectException(PublicationReasonCannotMatchTitle::class);

        $note->ensurePublicationRules();
    }

    public function test_published_note_accepts_a_valid_reason(): void
    {
        $note = Note::create(
            userId: UserId::fromInt(1),
            title: 'Quarterly roadmap',
            content: 'Roadmap details.',
            noteStatus: NoteStatus::Published,
            isPinned: false,
            publishedAt: null,
            publicationReason: new PublicationReason(
                PublicationReasonType::Decision,
                'Approved after leadership review.',
            ),
        );

        $note->ensurePublicationRules();

        $this->assertSame('Approved after leadership review.', $note->publicationReason()?->message());
    }
}
