<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Requests;

use App\Domain\Notes\Enums\NoteStatus;
use App\Domain\Notes\Enums\PublicationReasonType;
use App\Domain\Notes\ValueObjects\PublicationReason;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

class UpdateNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'content' => ['present', 'nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::enum(NoteStatus::class)],
            'is_pinned' => ['required', 'boolean'],
            'published_at' => ['present', 'nullable', 'date'],
            'publication_reason_type' => [
                Rule::requiredIf(fn (): bool => $this->input('status') === NoteStatus::Published->value),
                'nullable',
                'required_with:publication_reason_message',
                Rule::enum(PublicationReasonType::class),
            ],
            'publication_reason_message' => [
                Rule::requiredIf(fn (): bool => $this->input('status') === NoteStatus::Published->value),
                'nullable',
                'required_with:publication_reason_type',
                'string',
                'max:' . PublicationReason::MAX_MESSAGE_LENGTH,
                'not_regex:/(?:https?:\/\/|www\.)/i',
            ],
            'tag_ids' => ['present', 'array', 'max:5'],
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
            'publication_reason_type.required' => 'A publication reason type is required for published notes.',
            'publication_reason_type.required_with' => 'A publication reason type is required with the publication reason message.',
            'publication_reason_message.required' => 'A publication reason message is required for published notes.',
            'publication_reason_message.required_with' => 'A publication reason message is required with the publication reason type.',
            'publication_reason_message.not_regex' => 'The publication reason message cannot contain a URL.',
            'tag_ids.*.exists' => 'Each selected tag must already exist.',
        ];
    }

    public function title(): string
    {
        return $this->string('title')->toString();
    }

    public function content(): ?string
    {
        if ($this->input('content') === null) {
            return null;
        }

        return $this->string('content')->toString();
    }

    public function status(): NoteStatus
    {
        return NoteStatus::from($this->string('status')->toString());
    }

    public function isPinned(): bool
    {
        return $this->boolean('is_pinned');
    }

    public function publishedAt(): ?string
    {
        if ($this->input('published_at') === null) {
            return null;
        }

        return $this->string('published_at')->toString();
    }

    public function publicationReasonType(): ?PublicationReasonType
    {
        if ($this->input('publication_reason_type') === null) {
            return null;
        }

        return PublicationReasonType::from($this->string('publication_reason_type')->toString());
    }

    public function publicationReasonMessage(): ?string
    {
        if ($this->input('publication_reason_message') === null) {
            return null;
        }

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
