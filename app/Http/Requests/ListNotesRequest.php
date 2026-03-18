<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class ListNotesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|list<ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'string', 'max:100'],
            'status' => ['sometimes', 'string', 'in:draft,published,archived'],
            'tag' => ['sometimes', 'string', 'max:80'],
            'pinned' => ['sometimes', 'boolean'],
            'per_page' => ['sometimes', 'integer', 'between:1,50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    public function messages(): array
    {
        return [
            'status.in' => 'The status filter must be one of: draft, published, archived.',
            'per_page.between' => 'The per_page parameter must be between 1 and 50.',
        ];
    }
}
