<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use App\Services\CategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryServiceSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_categories_replaces_pivot(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $c1 = Category::create(['user_id' => $user->id, 'name' => 'X', 'description' => null]);
        $c2 = Category::create(['user_id' => $user->id, 'name' => 'Y', 'description' => null]);

        $task = Task::factory()->create(['user_id' => $user->id]);
        $service = app(CategoryService::class);
        $service->syncCategories($task, [$c1->id]);
        $task->refresh();
        $this->assertSame([$c1->id], $task->categories->pluck('id')->sort()->values()->all());

        $service->syncCategories($task, [$c2->id]);
        $task->refresh();
        $this->assertSame([$c2->id], $task->categories->pluck('id')->sort()->values()->all());
    }
}
