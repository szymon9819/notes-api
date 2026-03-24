<?php

declare(strict_types=1);

namespace App\Application\Notes\DTO;

/**
 * @template TItem
 */
final readonly class PaginatedData
{
    /**
     * @param  list<TItem>  $items
     * @param  array<string, string|null>  $links
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public array $items,
        public array $links,
        public array $meta,
    ) {}
}
