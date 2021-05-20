<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesGenresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories_genres', function (Blueprint $table) {
            $table->uuid('category_id')->index();
            $table->uuid('genre_id')->index();

            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('genre_id')->references('id')->on('genres');

            $table->unique(['category_id', 'genre_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories_genres');
    }
}
