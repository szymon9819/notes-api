<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Requests;

use App\Domain\Notes\Enums\NoteStatus;
use App\Domain\Notes\Enums\PublicationReasonType;
use App\Domain\Notes\ValueObjects\PublicationReason;
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
            'publication_reason_type' => [
                'required_if:status,published',
                'required_with:publication_reason_message',
                Rule::enum(PublicationReasonType::class),
            ],
            'publication_reason_message' => [
                'required_if:status,published',
                'required_with:publication_reason_type',
                'string',
                'max:' . PublicationReason::MAX_MESSAGE_LENGTH,
                'not_regex:/(?:https?:\/\/|www\.)/i',
            ],
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
            'publication_reason_type.required_if' => 'A publication reason type is required for published notes.',
            'publication_reason_type.required_with' => 'A publication reason type is required with the publication reason message.',
            'publication_reason_message.required_if' => 'A publication reason message is required for published notes.',
            'publication_reason_message.required_with' => 'A publication reason message is required with the publication reason type.',
            'publication_reason_message.not_regex' => 'The publication reason message cannot contain a URL.',
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

    public function hasPublicationReasonType(): bool
    {
        return $this->exists('publication_reason_type');
    }

    public function publicationReasonType(): PublicationReasonType
    {
        return PublicationReasonType::from($this->string('publication_reason_type')->toString());
    }

    public function hasPublicationReasonMessage(): bool
    {
        return $this->exists('publication_reason_message');
    }

    public function publicationReasonMessage(): string
    {
        return $this->string('publication_reason_message')->toString();
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
