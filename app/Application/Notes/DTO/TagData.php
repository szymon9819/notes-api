<?php

declare(strict_types=1);

namespace App\Application\Notes\DTO;

final readonly class TagData
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public ?int $notesCount = null,
    ) {}
}
