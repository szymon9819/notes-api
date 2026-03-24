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

    public function hasSearchTerm(): bool
    {
        return $this->exists('search');
    }

    public function searchTerm(): string
    {
        return $this->string('search')->toString();
    }

    public function hasStatusFilter(): bool
    {
        return $this->exists('status');
    }

    public function statusFilter(): NoteStatus
    {
        return NoteStatus::from($this->string('status')->toString());
    }

    public function hasTagFilter(): bool
    {
        return $this->exists('tag');
    }

    public function tagFilter(): string
    {
        return $this->string('tag')->toString();
    }

    public function hasPinnedFilter(): bool
    {
        return $this->exists('pinned');
    }

    public function pinnedFilter(): bool
    {
        return $this->boolean('pinned');
    }
}
