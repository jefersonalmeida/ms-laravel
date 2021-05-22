<?php

namespace App\Http\Resources;

use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Genre */
class GenreResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        // return parent::toArray($request);
        /*return [
            'id' => $this->id,
            'is_active' => $this->is_active,
            'name' => $this->name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
        ];*/
        return parent::toArray($request) + [
                // 'categories' => CategoryResource::collection($this->whenLoaded('categories')),
                'categories' => CategoryResource::collection($this->categories)
            ];
    }
}
