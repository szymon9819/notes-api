<?php

declare(strict_types=1);

namespace Tests\Unit\Unit\Application\Notes;

use App\Application\Notes\Contracts\TagQueryRepository;
use App\Application\Notes\DTO\TagData;
use App\Application\Notes\Queries\ListTags\ListTagsHandler;
use App\Application\Notes\Queries\ListTags\ListTagsQuery;
use PHPUnit\Framework\TestCase;

final class ListTagsHandlerTest extends TestCase
{
    public function test_it_returns_tags_from_the_repository(): void
    {
        $tags = [
            new TagData(1, 'Backend', 'backend', 2),
        ];
        $tagQueryRepository = $this->createMock(TagQueryRepository::class);
        $tagQueryRepository
            ->expects($this->once())
            ->method('allForUser')
            ->willReturn($tags);

        $listTagsHandler = new ListTagsHandler($tagQueryRepository);

        $result = $listTagsHandler->handle(new ListTagsQuery(userId: 1));

        $this->assertSame($tags, $result);
    }
}
