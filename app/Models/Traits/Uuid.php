<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;

trait Uuid
{
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->incrementing = false;
            $model->{$model->getKeyName()} = Str::uuid()->toString();
        });
    }
}
