<?php

namespace Tests\Stubs\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CategoryStub extends Model
{
    protected $table = 'categories_stubs';
    protected $fillable = ['name', 'description'];

    public static function createTable()
    {
        Schema::create('categories_stubs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });
        // (new \CreateCategoriesTable())->up();
    }

    public static function dropTable()
    {
        Schema::dropIfExists('categories_stubs');
        // (new \CreateCategoriesTable())->down();
    }
}
