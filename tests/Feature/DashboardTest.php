<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_dashboard_is_displayed_for_authenticated_user(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-18 12:00:00', 'Europe/Istanbul'));

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Hoş Geldiniz', false);
        $response->assertSee('Haftalık özet', false);
        $response->assertSee('yüksek öncelik', false);
        $response->assertSee('Bugün son tarih', false);
    }

    public function test_dashboard_week_summary_reflects_tasks_and_events(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-18 12:00:00', 'Europe/Istanbul'));

        $user = User::factory()->create();
        $weekStart = Carbon::now()->startOfWeek();

        Event::create([
            'user_id' => $user->id,
            'title' => 'Hafta içi toplantı',
            'description' => null,
            'start_date' => $weekStart->copy()->addDays(2)->setTime(10, 0),
            'end_date' => $weekStart->copy()->addDays(2)->setTime(11, 0),
            'location' => null,
            'all_day' => false,
        ]);

        Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Raporu bitir',
            'due_date' => $weekStart->copy()->addDays(3)->setTime(17, 0),
            'priority' => 3,
            'is_completed' => false,
            'status' => 'pending',
        ]);

        Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Bugünlük iş',
            'due_date' => Carbon::now()->setTime(18, 0),
            'priority' => 2,
            'is_completed' => false,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Hafta içi toplantı', false);
        $response->assertSee('Raporu bitir', false);
        $response->assertSee('Bugünlük iş', false);
        $content = $response->getContent();
        $this->assertStringContainsString('>1<', $content, 'Yüksek öncelik bu hafta kartında en az 1 görev beklenir');
        $this->assertStringContainsString('>1<', $content, 'Bugün son tarih kartında en az 1 görev beklenir');
    }
}
