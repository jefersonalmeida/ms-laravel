<?php

namespace App\Http\Controllers\Api;

use App\Models\Video;
use App\Rules\GenresHasCategoriesRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
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

    protected function addRuleIfGenreHasCategories(Request $request)
    {
        $categories = $request->get('category_ids');
        $this->rules['genre_ids'][] = new GenresHasCategoriesRule(
            is_array($categories) ? $categories : []
        );
    }

    /**
     * @throws Throwable
     * @throws ValidationException
     */
    public function store(Request $request): Model
    {
        /** @var Video $model */
        $this->addRuleIfGenreHasCategories($request);
        $data = $this->validate($request, $this->rulesStore());
        $model = $this->model()::create($data);
        $model->refresh();
        return $model;
    }

    /**
     * @throws Throwable
     * @throws ValidationException
     */
    public function update(Request $request, $id): Model
    {
        /** @var Video $model */
        $this->addRuleIfGenreHasCategories($request);
        $data = $this->validate($request, $this->rulesUpdate());
        $model = $this->findOrFail($id);
        $model->update($data);
        return $model;
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
}
