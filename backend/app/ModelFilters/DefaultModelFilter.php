<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;
use Illuminate\Support\Str;

abstract class DefaultModelFilter extends ModelFilter
{
    protected array $sortable = [];

    public function setup()
    {
        $this->blacklistMethod('isSortable');
        $noSort = $this->input('sort', '') === '';
        if ($noSort) {
            $this->orderBy('created_at', 'DESC');
        }
    }

    public function sort($column)
    {
        if (method_exists($this, $method = 'sortBy' . Str::studly($column))) {
            $this->$method();
        }

        if ($this->isSortable($column)) {
            $dir = strtolower($this->input('dir', 'asc')) === 'asc' ? 'ASC' : 'DESC';
            $this->orderBy($column, $dir);
        }
    }

    private function isSortable($column): bool
    {
        return in_array($column, $this->sortable);
    }
}
