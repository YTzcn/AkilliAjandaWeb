<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskIndexFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_tasks_index_filters_by_status(): void
    {
        $user = User::factory()->create();
        Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Bekleyen iş',
            'status' => 'pending',
            'due_date' => now()->addDay(),
        ]);
        Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Devam eden iş',
            'status' => 'in-progress',
            'due_date' => now()->addDays(2),
        ]);

        $response = $this->actingAs($user)->get(route('tasks.index', ['status' => 'pending']));

        $response->assertOk();
        $response->assertSee('Bekleyen iş');
        $response->assertDontSee('Devam eden iş');
    }

    public function test_tasks_index_sorts_by_title_descending(): void
    {
        $user = User::factory()->create();
        Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Zebra',
            'due_date' => now()->addDay(),
        ]);
        Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Alpha',
            'due_date' => now()->addDay(),
        ]);

        $response = $this->actingAs($user)->get(route('tasks.index', [
            'sort' => 'title',
            'dir' => 'desc',
        ]));

        $response->assertOk();
        $content = $response->getContent();
        $posZ = strpos($content, 'Zebra');
        $posA = strpos($content, 'Alpha');
        $this->assertNotFalse($posZ);
        $this->assertNotFalse($posA);
        $this->assertLessThan($posA, $posZ, 'Zebra başlığı tabloda Alpha’dan önce gelmeli (azalan sıra)');
    }

    public function test_tasks_index_rejects_invalid_sort_query(): void
    {
        $user = User::factory()->create();
        Task::factory()->create(['user_id' => $user->id, 'due_date' => now()->addDay()]);

        $response = $this->actingAs($user)->get(route('tasks.index', ['sort' => 'not_a_column']));

        $response->assertSessionHasErrors('sort');
    }

    public function test_tasks_index_filters_by_text_query(): void
    {
        $user = User::factory()->create();
        Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Alışveriş listesi',
            'description' => 'Süt ve ekmek',
            'due_date' => now()->addDay(),
        ]);
        Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Toplantı',
            'description' => 'Haftalık sync',
            'due_date' => now()->addDays(2),
        ]);

        $response = $this->actingAs($user)->get(route('tasks.index', ['q' => 'ekmek']));

        $response->assertOk();
        $response->assertSee('Alışveriş listesi');
        $response->assertDontSee('Toplantı');
    }
}

