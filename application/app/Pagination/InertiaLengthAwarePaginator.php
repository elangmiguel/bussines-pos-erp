<?php

namespace App\Pagination;

use Illuminate\Pagination\LengthAwarePaginator;

class InertiaLengthAwarePaginator extends LengthAwarePaginator
{
    /**
     * Serialize to the { data, links, meta } structure expected by Svelte pages.
     */
    public function toInertia(): array
    {
        return [
            'data'  => $this->items(),
            'links' => $this->linkCollection()->toArray(),
            'meta'  => [
                'current_page' => $this->currentPage(),
                'last_page'    => $this->lastPage(),
                'total'        => $this->total(),
                'from'         => $this->firstItem() ?? 0,
                'to'           => $this->lastItem() ?? 0,
                'per_page'     => $this->perPage(),
            ],
        ];
    }
}
