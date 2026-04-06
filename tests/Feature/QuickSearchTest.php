<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuickSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_quick_search_requires_authentication(): void
    {
        $this->getJson(route('search.quick', ['q' => 'x']))
            ->assertUnauthorized();
    }

    public function test_quick_search_validates_query(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->getJson(route('search.quick'))
            ->assertStatus(422);
    }

    public function test_quick_search_returns_grouped_hits(): void
    {
        $user = User::factory()->create();
        Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Özel rapor hazırla',
            'description' => 'Q4 özeti',
            'due_date' => now()->addDay(),
        ]);
        Event::create([
            'user_id' => $user->id,
            'title' => 'Rapor sunumu',
            'description' => 'Yönetim kurulu',
            'start_date' => now()->addDays(2),
            'end_date' => now()->addDays(2)->addHour(),
            'all_day' => false,
        ]);

        $response = $this->actingAs($user)->getJson(route('search.quick', ['q' => 'rapor']));

        $response->assertOk();
        $response->assertJsonPath('tasks.0.title', 'Özel rapor hazırla');
        $response->assertJsonPath('events.0.title', 'Rapor sunumu');
    }
}
