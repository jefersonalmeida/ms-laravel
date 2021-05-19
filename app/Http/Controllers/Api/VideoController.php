<?php

namespace App\Http\Controllers\Api;

use App\Models\Video;

class VideoController extends BasicCRUDController
{
    private array $rules;

    public function __construct()
    {
        $this->rules = [
            'title' => ['required', 'max:255'],
            'description' => ['required'],
            'year_launched' => ['required', 'date_format:Y'],
            'opened' => ['boolean'],
            'rating' => ['required', 'in:' . join(',', Video::RATING_LIST)],
            'duration' => ['required', 'integer'],
        ];
    }

    protected function model(): string
    {
        return Video::class;
    }

    protected function rulesStore(): array
    {
        return $this->rules;
    }

    protected function rulesUpdate(): array
    {
        return $this->rules;
    }

}
