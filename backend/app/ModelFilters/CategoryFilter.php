<?php

namespace App\ModelFilters;

class CategoryFilter extends DefaultModelFilter
{
    protected array $sortable = ['name', 'is_active', 'created_at'];

    public function search($search)
    {
        $this->query->where('name', 'LIKE', "%$search%");
    }
}
