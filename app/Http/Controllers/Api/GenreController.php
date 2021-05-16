<?php

namespace App\Http\Controllers\Api;

use App\Models\Genre;

class GenreController extends BasicCRUDController
{
    private array $rules = [
        'is_active' => ['boolean'],
        'name' => ['required', 'max:255'],
    ];

    protected function model(): string
    {
        return Genre::class;
    }

    protected function rulesStore(): array
    {
        return $this->rules;
    }

    protected function rulesUpdate(): array
    {
        return $this->rules;
    }
}
