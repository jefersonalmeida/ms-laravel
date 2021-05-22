<?php

namespace App\Http\Resources;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Video */
class VideoResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'year_launched' => $this->year_launched,
            'opened' => $this->opened,
            'rating' => $this->rating,
            'duration' => $this->duration,
            'thumb_file' => $this->thumb_file,
            'banner_file' => $this->banner_file,
            'trailer_file' => $this->trailer_file,
            'video_file' => $this->video_file,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'banner_file_url' => $this->banner_file_url,
            'thumb_file_url' => $this->thumb_file_url,
            'trailer_file_url' => $this->trailer_file_url,
            'video_file_url' => $this->video_file_url,

            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'genres' => GenreResource::collection($this->whenLoaded('genres')),
        ];*/
        return parent::toArray($request) + [
                'thumb_file_url' => $this->thumb_file_url,
                'banner_file_url' => $this->banner_file_url,
                'trailer_file_url' => $this->trailer_file_url,
                'video_file_url' => $this->video_file_url,
                // 'categories' => CategoryResource::collection($this->whenLoaded('categories')),
                // 'genres' => GenreResource::collection($this->whenLoaded('genres')),
                'categories' => CategoryResource::collection($this->categories),
                'genres' => GenreResource::collection($this->genres),
            ];
    }
}
