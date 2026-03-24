<?php

declare(strict_types=1);

namespace App\Application\Notes\Exceptions;

use App\Application\Common\Exceptions\ApplicationException;

final class NoteNotFound extends ApplicationException
{
    public static function forId(int $noteId): self
    {
        return new self(sprintf('Note [%d] was not found.', $noteId));
    }

    public static function forCreatedNote(): self
    {
        return new self('Created note could not be reloaded.');
    }
}
