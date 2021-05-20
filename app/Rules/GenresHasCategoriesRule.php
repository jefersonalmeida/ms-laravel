<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GenresHasCategoriesRule implements Rule
{
    private array $categoryIds;
    private array $genreIds;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(array $categoryIds)
    {
        $this->categoryIds = array_unique($categoryIds);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        if (!is_array($value)) {
            $value = [];
        }
        $this->genreIds = array_unique($value);
        if (!count($this->genreIds) || !count($this->categoryIds)) {
            return false;
        }

        $categories = [];
        foreach ($this->genreIds as $genreId) {
            $rows = $this->getRows($genreId);
            if (!$rows->count()) {
                return false;
            }
            array_push($categories, ...$rows->pluck('category_id')->toArray());
        }
        return count(array_unique($categories)) === count($this->categoryIds);
    }

    public function message(): string
    {
        // return 'A genre ID must be related at least a category ID.';
        return __('validation.genres_has_categories');
    }

    protected function getRows($genreId): Collection
    {
        return DB::table('categories_genres')
            ->where('genre_id', '=', $genreId)
            ->whereIn('category_id', $this->categoryIds)
            ->get();
    }
}
