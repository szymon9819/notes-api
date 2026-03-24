<?php

declare(strict_types=1);

namespace App\Domain\Notes\Entities;

use App\Domain\Common\ValueObjects\UserId;
use App\Domain\Notes\Enums\NoteStatus;
use App\Domain\Notes\Exceptions\PublicationReasonCannotMatchTitle;
use App\Domain\Notes\Exceptions\PublicationReasonRequired;
use App\Domain\Notes\ValueObjects\NoteId;
use App\Domain\Notes\ValueObjects\PublicationReason;
use App\Domain\Notes\ValueObjects\TagId;
use DateTimeImmutable;

final class Note
{
    /**
     * @param  list<TagId>  $tagIds
     */
    private function __construct(
        private readonly ?NoteId $noteId,
        private readonly UserId $userId,
        private string $title,
        private ?string $content,
        private NoteStatus $noteStatus,
        private bool $isPinned,
        private ?DateTimeImmutable $publishedAt,
        private ?PublicationReason $publicationReason,
        private array $tagIds = [],
    ) {}

    /**
     * @param  list<TagId>  $tagIds
     */
    public static function create(
        UserId $userId,
        string $title,
        ?string $content,
        NoteStatus $noteStatus,
        bool $isPinned,
        ?DateTimeImmutable $publishedAt,
        ?PublicationReason $publicationReason,
        array $tagIds = [],
    ): self {
        return new self(
            noteId: null,
            userId: $userId,
            title: $title,
            content: $content,
            noteStatus: $noteStatus,
            isPinned: $isPinned,
            publishedAt: $publishedAt,
            publicationReason: $publicationReason,
            tagIds: $tagIds,
        );
    }

    /**
     * @param  list<TagId>  $tagIds
     */
    public static function reconstitute(
        NoteId $noteId,
        UserId $userId,
        string $title,
        ?string $content,
        NoteStatus $noteStatus,
        bool $isPinned,
        ?DateTimeImmutable $publishedAt,
        ?PublicationReason $publicationReason,
        array $tagIds = [],
    ): self {
        return new self(
            noteId: $noteId,
            userId: $userId,
            title: $title,
            content: $content,
            noteStatus: $noteStatus,
            isPinned: $isPinned,
            publishedAt: $publishedAt,
            publicationReason: $publicationReason,
            tagIds: $tagIds,
        );
    }

    public function id(): ?NoteId
    {
        return $this->noteId;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function content(): ?string
    {
        return $this->content;
    }

    public function status(): NoteStatus
    {
        return $this->noteStatus;
    }

    public function isPinned(): bool
    {
        return $this->isPinned;
    }

    public function publishedAt(): ?DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function publicationReason(): ?PublicationReason
    {
        return $this->publicationReason;
    }

    /**
     * @return list<TagId>
     */
    public function tagIds(): array
    {
        return $this->tagIds;
    }

    public function changeTitle(string $title): void
    {
        $this->title = $title;
    }

    public function changeContent(?string $content): void
    {
        $this->content = $content;
    }

    public function changeStatus(NoteStatus $noteStatus): void
    {
        $this->noteStatus = $noteStatus;
    }

    public function changePinnedState(bool $isPinned): void
    {
        $this->isPinned = $isPinned;
    }

    public function changePublishedAt(?DateTimeImmutable $publishedAt): void
    {
        $this->publishedAt = $publishedAt;
    }

    public function changePublicationReason(?PublicationReason $publicationReason): void
    {
        $this->publicationReason = $publicationReason;
    }

    /**
     * @param  list<TagId>  $tagIds
     */
    public function syncTags(array $tagIds): void
    {
        $this->tagIds = $tagIds;
    }

    public function ensurePublishedAt(DateTimeImmutable $now): void
    {
        if ($this->noteStatus->isPublished() && !$this->publishedAt instanceof DateTimeImmutable) {
            $this->publishedAt = $now;
        }
    }

    public function ensurePublicationRules(): void
    {
        if (!$this->noteStatus->isPublished()) {
            return;
        }

        if (!$this->publicationReason instanceof PublicationReason) {
            throw PublicationReasonRequired::forPublishedNote();
        }

        if ($this->publicationReason->matchesTitle($this->title)) {
            throw PublicationReasonCannotMatchTitle::becauseMessageMatchesTitle();
        }
    }
}
