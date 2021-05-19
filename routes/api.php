<?php

use App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

$exceptCreateAndEdit = ['create', 'edit'];

Route::resource('categories', Api\CategoryController::class)->except($exceptCreateAndEdit);
Route::resource('genres', Api\GenreController::class)->except($exceptCreateAndEdit);
Route::resource('cast-members', Api\CastMemberController::class)->except($exceptCreateAndEdit);
Route::resource('videos', Api\VideoController::class)->except($exceptCreateAndEdit);
