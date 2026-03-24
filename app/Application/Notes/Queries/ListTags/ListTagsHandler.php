<?php

declare(strict_types=1);

namespace App\Application\Notes\Queries\ListTags;

use App\Application\Common\CQRS\QueryHandler;
use App\Application\Notes\Contracts\TagQueryRepository;
use App\Application\Notes\DTO\TagData;

final readonly class ListTagsHandler implements QueryHandler
{
    public function __construct(
        private TagQueryRepository $tagQueryRepository,
    ) {}

    /**
     * @return list<TagData>
     */
    public function handle(ListTagsQuery $listTagsQuery): array
    {
        return $this->tagQueryRepository->allForUser($listTagsQuery->userId);
    }
}
