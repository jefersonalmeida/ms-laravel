<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class GenreController extends Controller
{
    private array $rules = [
        'name' => ['required', 'max:255'],
        'is_active' => ['boolean'],
    ];

    public function index()
    {
        return Genre::all();
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->rules);
        return Genre::create($request->all());
    }

    public function show(Genre $genre): Genre
    {
        return $genre;
    }

    /**
     * @throws ValidationException
     */
    public function update(Request $request, Genre $genre): Genre
    {
        $this->validate($request, $this->rules);
        $genre->update($request->all());
        return $genre;
    }

    public function destroy(Genre $genre): Response
    {
        $genre->delete();
        return response()->noContent();
    }
}
