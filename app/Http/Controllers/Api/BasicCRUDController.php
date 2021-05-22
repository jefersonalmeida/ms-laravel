<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use ReflectionClass;

abstract class BasicCRUDController extends Controller
{
    protected int $paginationSize = 15;

    protected abstract function model();

    protected abstract function rulesStore();

    protected abstract function rulesUpdate();

    protected abstract function resource();

    protected abstract function resourceCollection();

    public function index()
    {
        $data = !$this->paginationSize ? $this->model()::all() : $this->model()::paginate($this->paginationSize);

        $collectionClass = $this->resourceCollection();
        $refClass = new ReflectionClass($this->resourceCollection());
        return $refClass->isSubclassOf(ResourceCollection::class)
            ? new $collectionClass($data)
            : $collectionClass::collection($data);
    }

    protected function findOrFail($id)
    {
        $model = $this->model();
        $keyName = (new $model)->getRouteKeyName();
        return $this->model()::where($keyName, '=', $id)->firstOrFail();
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $data = $this->validate($request, $this->rulesStore());
        $model = $this->model()::create($data);
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
}
