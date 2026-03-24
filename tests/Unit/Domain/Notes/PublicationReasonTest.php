<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Notes;

use App\Domain\Notes\Enums\PublicationReasonType;
use App\Domain\Notes\Exceptions\InvalidPublicationReasonMessage;
use App\Domain\Notes\ValueObjects\PublicationReason;
use PHPUnit\Framework\TestCase;

final class PublicationReasonTest extends TestCase
{
    public function test_it_normalizes_and_exposes_the_message(): void
    {
        $publicationReason = new PublicationReason(
            PublicationReasonType::Knowledge,
            '  Share onboarding notes with support.  ',
        );

        $this->assertSame(PublicationReasonType::Knowledge, $publicationReason->type());
        $this->assertSame('Share onboarding notes with support.', $publicationReason->message());
    }

    public function test_it_rejects_an_empty_message(): void
    {
        $this->expectException(InvalidPublicationReasonMessage::class);

        new PublicationReason(PublicationReasonType::Decision, '   ');
    }

    public function test_it_rejects_messages_longer_than_eighty_characters(): void
    {
        $this->expectException(InvalidPublicationReasonMessage::class);

        new PublicationReason(
            PublicationReasonType::Announcement,
            str_repeat('a', PublicationReason::MAX_MESSAGE_LENGTH + 1),
        );
    }

    public function test_it_rejects_messages_containing_a_url(): void
    {
        $this->expectException(InvalidPublicationReasonMessage::class);

        new PublicationReason(PublicationReasonType::Reminder, 'Check https://example.com before publishing.');
    }

    public function test_it_can_be_built_from_nullable_values(): void
    {
        $publicationReason = PublicationReason::fromNullable(
            PublicationReasonType::Decision,
            '  Approved in review.  ',
        );

        $this->assertInstanceOf(PublicationReason::class, $publicationReason);
        $this->assertSame('Approved in review.', $publicationReason->message());
    }

    public function test_it_returns_null_when_both_nullable_values_are_missing(): void
    {
        $this->assertNotInstanceOf(PublicationReason::class, PublicationReason::fromNullable(null, null));
    }
}
