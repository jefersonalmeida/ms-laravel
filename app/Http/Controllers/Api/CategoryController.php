<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    private array $rules = [
        'name' => ['required', 'max:255'],
        'is_active' => ['boolean'],
        'description' => ['nullable']
    ];

    public function index()
    {
        return Category::all();
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->rules);
        $model = Category::create($request->all());
        return $model->refresh();
    }

    public function show(Category $category): Category
    {
        return $category;
    }

    /**
     * @throws ValidationException
     */
    public function update(Request $request, Category $category): Category
    {
        $this->validate($request, $this->rules);
        $category->update($request->all());
        return $category;
    }

    public function destroy(Category $category): Response
    {
        $category->delete();
        return response()->noContent();
    }
}
