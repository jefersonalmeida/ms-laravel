<?php

namespace App\Models;

use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperCastMember
 */
class CastMember extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Uuid;

    const TYPE_DIRECTOR = 1;
    const TYPE_ACTOR = 2;

    public static array $types = [
        self::TYPE_DIRECTOR,
        self::TYPE_ACTOR,
    ];

    public $incrementing = false;

    protected $fillable = [
        'name',
        'type',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'id' => 'string',
        'type' => 'integer',
        'deleted_at' => 'datetime',
    ];
}
