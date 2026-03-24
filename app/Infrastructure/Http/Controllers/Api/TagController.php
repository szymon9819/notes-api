<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\Api;

use App\Application\Common\CQRS\QueryBus;
use App\Application\Notes\DTO\TagData;
use App\Application\Notes\Queries\ListTags\ListTagsQuery;
use App\Domain\Common\ValueObjects\UserId;
use App\Infrastructure\Http\Controllers\Controller;
use App\Infrastructure\Presentation\Api\TagDataPresenter;
use App\Persistence\Eloquent\Models\User;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Tags', 'Reference data for note categorization.', 20)]
class TagController extends Controller
{
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly TagDataPresenter $tagDataPresenter,
    ) {}

    #[Endpoint('listTags', 'List tags', 'Returns all available tags with the number of related notes.')]
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $result = $this->queryBus->ask(new ListTagsQuery(
            userId: UserId::fromInt($user->id),
        ));

        if (!is_array($result)) {
            abort(500);
        }

        $tags = [];

        foreach ($result as $tag) {
            if (!$tag instanceof TagData) {
                abort(500);
            }

            $tags[] = $tag;
        }

        return response()->json($this->tagDataPresenter->presentList($tags));
    }
}
