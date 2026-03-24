<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class PaginatedRequest extends FormRequest
{
    protected const int DEFAULT_PER_PAGE = 10;

    protected const int MAX_PER_PAGE = 50;

    protected function defaultPerPageValue(): int
    {
        return static::DEFAULT_PER_PAGE;
    }

    protected function maxPerPageValue(): int
    {
        return static::MAX_PER_PAGE;
    }

    /**
     * @return array{per_page: list<string>}
     */
    protected function paginationRules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'between:1,' . $this->maxPerPageValue()],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function paginationMessages(): array
    {
        return [
            'per_page.between' => 'The per_page parameter must be between 1 and ' . $this->maxPerPageValue() . '.',
        ];
    }

    public function perPage(): int
    {
        return $this->integer('per_page', $this->defaultPerPageValue());
    }
}
