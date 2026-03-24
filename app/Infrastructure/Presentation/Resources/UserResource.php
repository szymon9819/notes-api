<?php

declare(strict_types=1);

namespace App\Infrastructure\Presentation\Resources;

use App\Persistence\Eloquent\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/** @mixin User */
class UserResource extends JsonResource
{
    /**
     * @return array{id: int, name: string, email: string}
     */
    #[Override]
    public function toArray(Request $request): array
    {
        /** @var User $user */
        $user = $this->resource;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }
}
