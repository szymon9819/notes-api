<?php

declare(strict_types=1);

namespace App\Application\Notes\Commands\UpdateNote;

use App\Application\Common\CQRS\Command;
use App\Domain\Common\ValueObjects\UserId;
use App\Domain\Notes\Enums\NoteStatus;
use App\Domain\Notes\Enums\PublicationReasonType;
use App\Domain\Notes\ValueObjects\NoteId;
use DateTimeImmutable;

final readonly class UpdateNoteCommand implements Command
{
    /**
     * @param  list<int>  $tagIds
     */
    public function __construct(
        public UserId $userId,
        public NoteId $noteId,
        public ?string $content,
        public string $title,
        public NoteStatus $status,
        public bool $isPinned,
        public ?DateTimeImmutable $publishedAt,
        public ?PublicationReasonType $publicationReasonType,
        public ?string $publicationReasonMessage,
        public array $tagIds,
    ) {}
}
