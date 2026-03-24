<?php

declare(strict_types=1);

namespace Tests\Unit\Unit\Domain\Common;

use App\Domain\Common\Exceptions\InvalidUserId;
use App\Domain\Common\ValueObjects\UserId;
use App\Domain\Notes\Exceptions\InvalidNoteId;
use App\Domain\Notes\Exceptions\InvalidTagId;
use App\Domain\Notes\ValueObjects\NoteId;
use App\Domain\Notes\ValueObjects\TagId;
use PHPUnit\Framework\TestCase;

final class IntIdTest extends TestCase
{
    public function test_it_creates_a_positive_user_id(): void
    {
        $userId = UserId::fromInt(1);

        $this->assertSame(1, $userId->value);
        $this->assertInstanceOf(UserId::class, $userId);
    }

    public function test_it_creates_a_positive_note_id(): void
    {
        $noteId = NoteId::fromInt(2);

        $this->assertSame(2, $noteId->value);
        $this->assertInstanceOf(NoteId::class, $noteId);
    }

    public function test_it_creates_a_positive_tag_id(): void
    {
        $tagId = TagId::fromInt(3);

        $this->assertSame(3, $tagId->value);
        $this->assertInstanceOf(TagId::class, $tagId);
    }

    public function test_it_rejects_a_non_positive_user_id(): void
    {
        $this->expectException(InvalidUserId::class);

        UserId::fromInt(0);
    }

    public function test_it_rejects_a_non_positive_note_id(): void
    {
        $this->expectException(InvalidNoteId::class);

        NoteId::fromInt(0);
    }

    public function test_it_rejects_a_non_positive_tag_id(): void
    {
        $this->expectException(InvalidTagId::class);

        TagId::fromInt(0);
    }
}
