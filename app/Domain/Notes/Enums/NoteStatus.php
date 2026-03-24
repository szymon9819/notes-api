<?php

declare(strict_types=1);

namespace App\Domain\Notes\Enums;

enum NoteStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';

    public function isPublished(): bool
    {
        return $this === self::Published;
    }
}
