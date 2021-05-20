<?php

namespace App\Http\Controllers\Api;

use App\Models\Video;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class VideoController extends BasicCRUDController
{
    private array $rules;

    public function __construct()
    {
        $this->rules = [
            'title' => ['required', 'max:255'],
            'description' => ['required'],
            'year_launched' => ['required', 'date_format:Y'],
            'opened' => ['boolean'],
            'rating' => ['required', 'in:' . join(',', Video::RATING_LIST)],
            'duration' => ['required', 'integer'],
            'category_ids' => ['required', 'array', 'exists:categories,id,deleted_at,NULL'],
            'genre_ids' => ['required', 'array', 'exists:genres,id,deleted_at,NULL'],
        ];
    }

    /**
     * @throws Throwable
     * @throws ValidationException
     */
    public function store(Request $request): Model
    {
        /** @var Video $model */
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
        /** @var Video $model */
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
        return Video::class;
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
        $model->genres()->sync($request->get('genre_ids'));
    }

}
