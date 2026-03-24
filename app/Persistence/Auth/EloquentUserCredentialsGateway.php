<?php

declare(strict_types=1);

namespace App\Persistence\Auth;

use App\Application\Auth\Contracts\UserCredentialsGateway;
use App\Application\Auth\Exceptions\InvalidCredentials;
use App\Domain\Auth\Entities\UserIdentity;
use App\Domain\Common\ValueObjects\UserId;
use App\Persistence\Eloquent\Models\User;
use Illuminate\Support\Facades\Hash;

final class EloquentUserCredentialsGateway implements UserCredentialsGateway
{
    /**
     * @throws InvalidCredentials
     */
    public function authenticate(string $email, string $password): UserIdentity
    {
        $user = User::query()->where('email', $email)->first();

        if (!($user instanceof User) || !Hash::check($password, $user->password)) {
            throw InvalidCredentials::forEmail($email);
        }

        return new UserIdentity(
            id: UserId::fromInt($user->id),
            name: $user->name,
            email: $user->email,
        );
    }
}
