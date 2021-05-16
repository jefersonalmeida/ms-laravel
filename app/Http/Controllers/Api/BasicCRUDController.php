<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

abstract class BasicCRUDController extends Controller
{
    protected abstract function model();

    protected abstract function rulesStore();

    protected abstract function rulesUpdate();

    public function index()
    {
        return $this->model()::all();
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
    public function store(Request $request): Model
    {
        $data = $this->validate($request, $this->rulesStore());
        $model = $this->model()::create($data);
        return $model->refresh();
    }

    public function show($id): Model
    {
        return $this->findOrFail($id);
    }

    /**
     * @throws ValidationException
     */
    public function update(Request $request, $id): Model
    {
        $model = $this->findOrFail($id);
        $data = $this->validate($request, $this->rulesUpdate());
        $model->update($data);
        return $model;
    }

    public function destroy($id): Response
    {
        $model = $this->findOrFail($id);
        $model->delete();
        return response()->noContent();
    }
}
