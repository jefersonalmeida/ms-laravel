<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories_videos', function (Blueprint $table) {
            $table->uuid('category_id')->index();
            $table->uuid('video_id')->index();

            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('video_id')->references('id')->on('videos');

            $table->unique(['category_id', 'video_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories_videos');
    }
}
