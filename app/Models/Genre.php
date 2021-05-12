<?php

namespace App\Models;

use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperGenre
 */
class Genre extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Uuid;

    protected $keyType = 'string';
    protected $fillable = [
        'is_active',
        'name',
    ];

    protected $dates = [
        'deleted_at'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}
