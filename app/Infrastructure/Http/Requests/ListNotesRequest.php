<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Requests;

use App\Domain\Notes\Enums\NoteStatus;
use Illuminate\Contracts\Validation\Rule as ValidationRuleContract;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Override;

class ListNotesRequest extends PaginatedRequest
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
            'search' => ['sometimes', 'string', 'max:100'],
            'status' => ['sometimes', Rule::enum(NoteStatus::class)],
            'tag' => ['sometimes', 'string', 'max:80'],
            'pinned' => ['sometimes', 'boolean'],
        ] + $this->paginationRules();
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    public function messages(): array
    {
        return [
            'status.enum' => 'The status filter must be one of: draft, published, archived.',
        ] + $this->paginationMessages();
    }

    public function searchTerm(): ?string
    {
        if (!$this->exists('search')) {
            return null;
        }

        return $this->string('search')->toString();
    }

    public function statusFilter(): ?NoteStatus
    {
        if (!$this->exists('status')) {
            return null;
        }

        return NoteStatus::from($this->string('status')->toString());
    }

    public function tagFilter(): ?string
    {
        if (!$this->exists('tag')) {
            return null;
        }

        return $this->string('tag')->toString();
    }

    public function pinnedFilter(): ?bool
    {
        if (!$this->exists('pinned')) {
            return null;
        }

        return $this->boolean('pinned');
    }
}
