<?php

namespace App\Models;

use App\ModelFilters\CategoryFilter;
use App\Models\Traits\Uuid;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperCategory
 */
class Category extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Uuid;
    use Filterable;

    public $incrementing = false;

    protected $fillable = [
        'is_active',
        'name',
        'description',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'id' => 'string',
        'is_active' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function modelFilter(): ?string
    {
        return $this->provideFilter(CategoryFilter::class);
    }
}
