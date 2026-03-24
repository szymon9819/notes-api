<?php

declare(strict_types=1);

namespace App\Application\Notes\DTO;

final readonly class NoteData
{
    /**
     * @param  list<TagData>  $tags
     */
    public function __construct(
        public int $id,
        public int $userId,
        public string $title,
        public ?string $content,
        public string $status,
        public bool $isPinned,
        public ?string $publishedAt,
        public ?string $publicationReasonType,
        public ?string $publicationReasonMessage,
        public ?string $createdAt,
        public ?string $updatedAt,
        public UserData $user,
        public array $tags,
    ) {}
}
