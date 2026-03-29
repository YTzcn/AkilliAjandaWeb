<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService
    ) {}

    public function index(): View
    {
        $categories = $this->categoryService->listForUser();

        return view('categories.index', compact('categories'));
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->categoryService->create($request->validated());

        return redirect()->route('categories.index')->with('success', 'Kategori eklendi.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        abort_if($category->user_id !== auth()->id(), 403);

        if (! $this->categoryService->delete($category)) {
            return redirect()->route('categories.index')->with('error', 'Bu kategori silinemez.');
        }

        return redirect()->route('categories.index')->with('success', 'Kategori silindi.');
    }
}
