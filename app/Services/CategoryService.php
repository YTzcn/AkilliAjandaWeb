<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CategoryService
{
    public function listForUser(): Collection
    {
        return Category::query()
            ->where('user_id', Auth::id())
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): Category
    {
        $data['user_id'] = Auth::id();

        return Category::query()->create($data);
    }

    public function delete(Category $category): bool
    {
        if ($category->user_id !== Auth::id()) {
            return false;
        }

        return (bool) $category->delete();
    }

    /**
     * Görev veya etkinlik üzerindeki kategori ilişkisini kullanıcıya ait ID'lerle senkronlar.
     *
     * @param  array<int, int|string>  $categoryIds
     */
    public function syncCategories(Model $model, array $categoryIds): void
    {
        if (! method_exists($model, 'categories')) {
            throw new \InvalidArgumentException('Model categories() ilişkisine sahip olmalıdır.');
        }

        $model->categories()->sync($this->filterOwnedCategoryIds($categoryIds));
    }

    /**
     * @param  array<int, int|string>  $categoryIds
     * @return array<int, int>
     */
    public function filterOwnedCategoryIds(array $categoryIds): array
    {
        $categoryIds = array_values(array_unique(array_filter(array_map('intval', $categoryIds))));
        if ($categoryIds === []) {
            return [];
        }

        return Category::query()
            ->where('user_id', Auth::id())
            ->whereIn('id', $categoryIds)
            ->pluck('id')
            ->all();
    }
}
