<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
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
}
