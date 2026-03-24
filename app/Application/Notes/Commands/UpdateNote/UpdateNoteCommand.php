<?php

declare(strict_types=1);

namespace App\Application\Notes\Commands\UpdateNote;

use App\Application\Common\CQRS\Command;
use App\Domain\Notes\Enums\NoteStatus;
use App\Domain\Notes\Enums\PublicationReasonType;
use DateTimeImmutable;

final readonly class UpdateNoteCommand implements Command
{
    /**
     * @param  list<int>  $tagIds
     */
    public function __construct(
        public int $userId,
        public int $noteId,
        public bool $hasTitle,
        public ?string $title,
        public bool $hasContent,
        public ?string $content,
        public bool $hasStatus,
        public ?NoteStatus $status,
        public bool $hasPinnedState,
        public bool $isPinned,
        public bool $hasPublishedAt,
        public ?DateTimeImmutable $publishedAt,
        public bool $hasPublicationReasonType,
        public ?PublicationReasonType $publicationReasonType,
        public bool $hasPublicationReasonMessage,
        public ?string $publicationReasonMessage,
        public bool $hasTagIds,
        public array $tagIds,
    ) {}
}
