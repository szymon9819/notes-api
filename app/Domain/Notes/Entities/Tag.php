<?php

declare(strict_types=1);

namespace App\Domain\Notes\Entities;

use App\Domain\Notes\ValueObjects\TagId;

final readonly class Tag
{
    public function __construct(
        public TagId $id,
        public string $name,
        public string $slug,
        public ?int $notesCount = null,
    ) {}
}
