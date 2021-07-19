<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use ReflectionClass;

abstract class BasicCRUDController extends Controller
{
    protected int $defaultPerPage = 30;

    protected abstract function model();

    protected abstract function rulesStore();

    protected abstract function rulesUpdate();

    protected abstract function resource();

    protected abstract function resourceCollection();

    public function index(Request $request)
    {
        $perPage = (integer)$request->get('per_page', $this->defaultPerPage);
        $hasFilter = in_array(Filterable::class, class_uses($this->model()));

        $query = $this->queryBuilder();

        if ($hasFilter) {
            $query = $query->filter($request->all());
        }

        $data = $request->has('all') || !$this->defaultPerPage
            ? $query->get()
            : $query->paginate($perPage);

        $collectionClass = $this->resourceCollection();
        $refClass = new ReflectionClass($this->resourceCollection());
        return $refClass->isSubclassOf(ResourceCollection::class)
            ? new $collectionClass($data)
            : $collectionClass::collection($data);
    }

    protected function findOrFail($id): Model|Builder
    {
        $model = $this->model();
        $keyName = (new $model)->getRouteKeyName();
        return $this->queryBuilder()->where($keyName, '=', $id)->firstOrFail();
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $data = $this->validate($request, $this->rulesStore());
        $model = $this->queryBuilder()->create($data);
        $model->refresh();
        $resource = $this->resource();
        return new $resource($model);
    }

    public function show($id)
    {
        $model = $this->findOrFail($id);
        $resource = $this->resource();
        return new $resource($model);
    }

    /**
     * @throws ValidationException
     */
    public function update(Request $request, $id)
    {
        $model = $this->findOrFail($id);
        $data = $this->validate($request, $this->rulesUpdate());
        $model->update($data);
        $resource = $this->resource();
        return new $resource($model);
    }

    public function destroy($id): Response
    {
        $model = $this->findOrFail($id);
        $model->delete();
        return response()->noContent();
    }

    protected function queryBuilder(): Builder
    {
        return $this->model()::query();
    }
}
