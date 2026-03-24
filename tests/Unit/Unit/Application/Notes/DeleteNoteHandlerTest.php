<?php

declare(strict_types=1);

namespace Tests\Unit\Unit\Application\Notes;

use App\Application\Common\Contracts\TransactionManager;
use App\Application\Notes\Commands\DeleteNote\DeleteNoteCommand;
use App\Application\Notes\Commands\DeleteNote\DeleteNoteHandler;
use App\Application\Notes\Contracts\NoteCommandRepository;
use App\Application\Notes\Exceptions\NoteNotFound;
use App\Domain\Common\ValueObjects\UserId;
use App\Domain\Notes\ValueObjects\NoteId;
use PHPUnit\Framework\TestCase;

final class DeleteNoteHandlerTest extends TestCase
{
    public function test_it_throws_when_the_note_cannot_be_deleted(): void
    {
        $noteCommandRepository = $this->createMock(NoteCommandRepository::class);
        $noteCommandRepository
            ->expects($this->once())
            ->method('deleteOwnedById')
            ->willReturn(false);

        $deleteNoteHandler = new DeleteNoteHandler(
            $noteCommandRepository,
            new class() implements TransactionManager
            {
                public function run(callable $callback): mixed
                {
                    return $callback();
                }
            },
        );

        $this->expectException(NoteNotFound::class);

        $deleteNoteHandler->handle(new DeleteNoteCommand(
            userId: UserId::fromInt(1),
            noteId: NoteId::fromInt(99),
        ));
    }
}
