<?php

namespace Tests\Stubs\Controllers;

use App\Http\Controllers\Api\BasicCRUDController;
use Tests\Stubs\Models\CategoryStub;
use Tests\Stubs\Resources\CategoryStubResource;

class CategoryControllerStub extends BasicCRUDController
{
    private array $rules = [
        'name' => ['required', 'max:255'],
        'description' => ['nullable'],
    ];

    protected function model(): string
    {
        return CategoryStub::class;
    }

    protected function rulesStore(): array
    {
        return $this->rules;
    }

    protected function rulesUpdate(): array
    {
        return $this->rules;
    }

    protected function resourceCollection(): string
    {
        return $this->resource();
    }

    protected function resource(): string
    {
        return CategoryStubResource::class;
    }
}
