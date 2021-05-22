<?php

namespace Database\Seeders;

use App\Models\Genre;
use App\Models\Video;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Throwable;

class VideoSeeder extends Seeder
{
    /**
     * @var Genre[]|Collection
     */
    private $allGenres;

    private array $relations = [
        'genres_id' => [],
        'categories_id' => [],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     * @throws Throwable
     */
    public function run()
    {
        $dir = Storage::getDriver()->getAdapter()->getPathPrefix();
        File::deleteDirectory($dir, true);

        $this->allGenres = Genre::all();
        Model::reguard(); //mass assignment
        Video::factory()
            ->count(50)
            ->make()
            ->each(function (Video $video) {
                $this->fetchRelations();
                Video::create(
                    array_merge(
                        $video->toArray(), //thumb_file, banner_file
                        [
                            'thumb_file' => $this->getImageFile(),
                            'banner_file' => $this->getImageFile(),
                            'trailer_file' => $this->getVideoFile(),
                            'video_file' => $this->getVideoFile(),
                        ],
                        $this->relations
                    )
                );
            });
        Model::unguard();
    }


    public function fetchRelations()
    {
        $subGenres = $this->allGenres->random(5)->load('categories');
        $categoriesId = [];
        foreach ($subGenres as $genre) {
            array_push($categoriesId, ...$genre->categories->pluck('id')->toArray());
        }
        $categoriesId = array_unique($categoriesId);
        $genresId = $subGenres->pluck('id')->toArray();
        $this->relations['categories_id'] = $categoriesId;
        $this->relations['genres_id'] = $genresId;
    }

    public function getImageFile(): UploadedFile
    {
        return new UploadedFile(
            storage_path('faker/thumbs/Laravel-Framework.png'),
            'Laravel Framework.png'
        );
    }

    public function getVideoFile(): UploadedFile
    {
        return new UploadedFile(
            storage_path('faker/videos/01-Como vai funcionar os uploads.mp4'),
            '01-Como vai funcionar os uploads.mp4'
        );
    }
}
