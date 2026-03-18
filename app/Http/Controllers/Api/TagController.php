<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[Group('Tags', 'Reference data for note categorization.', 20)]
class TagController extends Controller
{
    #[Endpoint('listTags', 'List tags', 'Returns all available tags with the number of related notes.')]
    public function index(): AnonymousResourceCollection
    {
        return TagResource::collection(
            Tag::query()
                ->withCount('notes')
                ->orderBy('name')
                ->get(),
        );
    }
}
