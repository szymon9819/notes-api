<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers;

use App\Application\Auth\Contracts\ApiTokenIssuer;
use App\Application\Auth\Contracts\UserCredentialsGateway;
use App\Application\Common\Contracts\DateTimeProvider;
use App\Application\Common\Contracts\TransactionManager;
use App\Application\Common\CQRS\CommandBus;
use App\Application\Common\CQRS\QueryBus;
use App\Application\Notes\Contracts\NoteCommandRepository;
use App\Application\Notes\Contracts\NoteQueryRepository;
use App\Application\Notes\Contracts\TagQueryRepository;
use App\Infrastructure\Auth\SanctumApiTokenIssuer;
use App\Infrastructure\Common\LaravelDateTimeProvider;
use App\Infrastructure\CQRS\ConventionCommandBus;
use App\Infrastructure\CQRS\ConventionQueryBus;
use App\Persistence\Auth\EloquentUserCredentialsGateway;
use App\Persistence\Database\LaravelTransactionManager;
use App\Persistence\Eloquent\Repositories\EloquentNoteCommandRepository;
use App\Persistence\Eloquent\Repositories\EloquentNoteQueryRepository;
use App\Persistence\Eloquent\Repositories\EloquentTagQueryRepository;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Override;

final class CleanArchitectureServiceProvider extends ServiceProvider implements DeferrableProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(DateTimeProvider::class, LaravelDateTimeProvider::class);
        $this->app->bind(TransactionManager::class, LaravelTransactionManager::class);
        $this->app->bind(CommandBus::class, ConventionCommandBus::class);
        $this->app->bind(QueryBus::class, ConventionQueryBus::class);
        $this->app->bind(UserCredentialsGateway::class, EloquentUserCredentialsGateway::class);
        $this->app->bind(ApiTokenIssuer::class, SanctumApiTokenIssuer::class);
        $this->app->bind(NoteQueryRepository::class, EloquentNoteQueryRepository::class);
        $this->app->bind(NoteCommandRepository::class, EloquentNoteCommandRepository::class);
        $this->app->bind(TagQueryRepository::class, EloquentTagQueryRepository::class);
    }

    /**
     * @return list<class-string>
     */
    #[Override]
    public function provides(): array
    {
        return [
            DateTimeProvider::class,
            TransactionManager::class,
            CommandBus::class,
            QueryBus::class,
            UserCredentialsGateway::class,
            ApiTokenIssuer::class,
            NoteQueryRepository::class,
            NoteCommandRepository::class,
            TagQueryRepository::class,
        ];
    }
}
