<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\GenreResource;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class GenreController extends BasicCRUDController
{
    private array $rules = [
        'is_active' => ['boolean'],
        'name' => ['required', 'max:255'],
        'categories_id' => ['required', 'array', 'exists:categories,id,deleted_at,NULL'],
    ];

    /**
     * @throws Throwable
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        /** @var Genre $model */
        $data = $this->validate($request, $this->rulesStore());
        return DB::transaction(function () use ($data, $request) {
            $model = $this->model()::create($data);
            $this->handleRelations($model, $request);
            $model->refresh();
            $resource = $this->resource();
            return new $resource($model);
        });
    }

    /**
     * @throws Throwable
     * @throws ValidationException
     */
    public function update(Request $request, $id)
    {
        /** @var Genre $model */
        $data = $this->validate($request, $this->rulesUpdate());
        return DB::transaction(function () use ($id, $data, $request) {
            $model = $this->findOrFail($id);
            $model->update($data);
            $this->handleRelations($model, $request);
            $resource = $this->resource();
            return new $resource($model);
        });
    }

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

    /**
     * @param $model
     * @param Request $request
     */
    protected function handleRelations($model, Request $request): void
    {
        $model->categories()->sync($request->get('categories_id'));
    }

    protected function resourceCollection(): string
    {
        return $this->resource();
    }

    protected function resource(): string
    {
        return GenreResource::class;
    }
}
