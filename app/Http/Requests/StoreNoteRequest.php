<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\NoteStatus;
use Illuminate\Contracts\Validation\Rule as ValidationRuleContract;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

class StoreNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|ValidationRuleContract|list<ValidationRule|ValidationRuleContract|string>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'content' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::enum(NoteStatus::class)],
            'is_pinned' => ['sometimes', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'tag_ids' => ['sometimes', 'array', 'max:5'],
            'tag_ids.*' => ['integer', 'distinct', 'exists:tags,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    public function messages(): array
    {
        return [
            'status.enum' => 'The status must be one of: draft, published, archived.',
            'tag_ids.*.exists' => 'Each selected tag must already exist.',
        ];
    }

    public function title(): string
    {
        return $this->string('title')->toString();
    }

    public function status(): NoteStatus
    {
        return NoteStatus::from($this->string('status')->toString());
    }

    public function hasContent(): bool
    {
        return $this->exists('content');
    }

    public function contentIsNull(): bool
    {
        return $this->input('content') === null;
    }

    public function content(): string
    {
        return $this->string('content')->toString();
    }

    public function hasPinnedFlag(): bool
    {
        return $this->exists('is_pinned');
    }

    public function isPinned(): bool
    {
        return $this->boolean('is_pinned');
    }

    public function hasPublishedAt(): bool
    {
        return $this->exists('published_at');
    }

    public function publishedAtIsNull(): bool
    {
        return $this->input('published_at') === null;
    }

    public function publishedAt(): string
    {
        return $this->string('published_at')->toString();
    }

    /**
     * @return list<int>
     */
    public function tagIds(): array
    {
        $tagIds = [];

        foreach ($this->array('tag_ids') as $tagId) {
            if (is_int($tagId)) {
                $tagIds[] = $tagId;

                continue;
            }

            if (is_string($tagId) && is_numeric($tagId)) {
                $tagIds[] = (int) $tagId;
            }
        }

        return $tagIds;
    }
}
