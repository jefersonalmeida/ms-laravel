<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CastMemberResource;
use App\Models\CastMember;

class CastMemberController extends BasicCRUDController
{
    private array $rules;

    /**
     * CastMemberController constructor.
     */
    public function __construct()
    {
        $this->rules = [
            'name' => ['required', 'max:255'],
            'type' => ['required', 'in:' . join(',', CastMember::$types)],
        ];
    }

    protected function model(): string
    {
        return CastMember::class;
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
        return CastMemberResource::class;
    }
}
