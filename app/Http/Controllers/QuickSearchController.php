<?php

namespace App\Http\Controllers;

use App\Services\GlobalSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuickSearchController extends Controller
{
    public function __construct(
        protected GlobalSearchService $globalSearchService
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:1', 'max:200'],
        ]);

        return response()->json(
            $this->globalSearchService->quickResults($validated['q'])
        );
    }
}
