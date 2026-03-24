<?php

declare(strict_types=1);

namespace App\Persistence\Eloquent\Models;

use App\Domain\Notes\Enums\NoteStatus;
use Database\Factories\NoteFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Override;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string|null $content
 * @property NoteStatus $status
 * @property bool $is_pinned
 * @property Carbon|null $published_at
 * @property string|null $publication_reason_type
 * @property string|null $publication_reason_message
 * @property User|null $user
 * @property Collection<int, Tag> $tags
 */
final class Note extends Model
{
    /** @use HasFactory<NoteFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'status',
        'is_pinned',
        'published_at',
        'publication_reason_type',
        'publication_reason_message',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'status' => NoteStatus::class,
            'is_pinned' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function newFactory(): NoteFactory
    {
        return NoteFactory::new();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }
}
