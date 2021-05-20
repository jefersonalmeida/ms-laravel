<?php

namespace App\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class GenreController extends BasicCRUDController
{
    private array $rules = [
        'is_active' => ['boolean'],
        'name' => ['required', 'max:255'],
        'category_ids' => ['required', 'array', 'exists:categories,id,deleted_at,NULL'],
    ];


    /**
     * @throws Throwable
     * @throws ValidationException
     */
    public function store(Request $request): Model
    {
        /** @var Genre $model */
        $data = $this->validate($request, $this->rulesStore());
        return DB::transaction(function () use ($data, $request) {
            $model = $this->model()::create($data);
            $this->handleRelations($model, $request);
            $model->refresh();
            return $model;
        });
    }

    /**
     * @throws Throwable
     * @throws ValidationException
     */
    public function update(Request $request, $id): Model
    {
        /** @var Genre $model */
        $data = $this->validate($request, $this->rulesUpdate());
        return DB::transaction(function () use ($id, $data, $request) {
            $model = $this->findOrFail($id);
            $model->update($data);
            $this->handleRelations($model, $request);
            return $model;
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
        $model->categories()->sync($request->get('category_ids'));
    }
}
