<?php

declare(strict_types=1);

namespace App\Application\Notes\Contracts;

use App\Application\Notes\DTO\TagData;
use App\Domain\Common\ValueObjects\UserId;

interface TagQueryRepository
{
    /**
     * @return list<TagData>
     */
    public function allForUser(UserId $userId): array;
}
