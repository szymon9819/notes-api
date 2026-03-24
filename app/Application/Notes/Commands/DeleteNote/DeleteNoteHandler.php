<?php

declare(strict_types=1);

namespace App\Application\Notes\Commands\DeleteNote;

use App\Application\Common\Contracts\TransactionManager;
use App\Application\Common\CQRS\CommandHandler;
use App\Application\Notes\Contracts\NoteCommandRepository;
use App\Application\Notes\Exceptions\NoteNotFound;

final readonly class DeleteNoteHandler implements CommandHandler
{
    public function __construct(
        private NoteCommandRepository $noteCommandRepository,
        private TransactionManager $transactionManager,
    ) {}

    public function handle(DeleteNoteCommand $deleteNoteCommand): void
    {
        $deleted = $this->transactionManager->run(
            fn (): bool => $this->noteCommandRepository->deleteOwnedById(
                $deleteNoteCommand->userId,
                $deleteNoteCommand->noteId,
            ),
        );

        if (!$deleted) {
            throw NoteNotFound::forId($deleteNoteCommand->noteId->value);
        }
    }
}
