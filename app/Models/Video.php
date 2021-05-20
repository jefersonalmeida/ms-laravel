<?php

namespace App\Models;

use App\Models\Traits\UploadFiles;
use App\Models\Traits\Uuid;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * @mixin IdeHelperVideo
 */
class Video extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Uuid;
    use UploadFiles;

    const NO_RATING = 'L';
    const RATING_LIST = [self::NO_RATING, '10', '12', '14', '16', '18'];

    public $incrementing = false;

    protected $fillable = [
        'title',
        'description',
        'year_launched',
        'opened',
        'rating',
        'duration',
        'video_file',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'id' => 'string',
        'opened' => 'boolean',
        'year_launched' => 'integer',
        'duration' => 'integer',
        'deleted_at' => 'datetime',
    ];

    /**
     * @throws Throwable
     */
    public static function create(array $attributes = []): Video
    {
        $files = self::extractFiles($attributes);

        DB::beginTransaction();
        try {
            /** @var Video $model */
            $model = self::query()->create($attributes);
            self::handleRelations($model, $attributes);

            $model->uploadFiles($files);

            DB::commit();
            return $model;
        } catch (Exception $e) {
            if (isset($model)) {
                // excluir os arquivos
            }
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @throws Throwable
     */
    public function update(array $attributes = [], array $options = []): bool
    {
        DB::beginTransaction();
        try {
            $saved = parent::update($attributes, $options);
            self::handleRelations($this, $attributes);
            if ($saved) {
                // upload
                // excluir os antigos
            }
            DB::commit();
            return $saved;
        } catch (Exception $e) {
            // excluir os arquivos
            DB::rollBack();
            throw $e;
        }
    }


    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'categories_videos',
            'video_id',
            'category_id',
            'id',
            'id',
        )->withTrashed();
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(
            Genre::class,
            'genres_videos',
            'video_id',
            'genre_id',
            'id',
            'id',
        )->withTrashed();
    }


    public static function handleRelations(Video $model, array $attributes): void
    {
        if (isset($attributes['category_ids'])) {
            $model->categories()->sync($attributes['category_ids']);
        }
        if (isset($attributes['genre_ids'])) {
            $model->genres()->sync($attributes['genre_ids']);
        }
    }

    protected function uploadDir(): string
    {
        return $this->id;
    }

    protected static function fileFields(): array
    {
        return ['video_file'];
    }
}
