<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeriodReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_reports(): void
    {
        $this->get(route('reports.index'))
            ->assertRedirect();
    }

    public function test_index_shows_summary_for_own_data_only(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Benim görev',
            'due_date' => Carbon::parse('2026-05-10 12:00:00'),
            'is_completed' => false,
        ]);
        Task::factory()->create([
            'user_id' => $other->id,
            'title' => 'Başkasının görevi',
            'due_date' => Carbon::parse('2026-05-11 12:00:00'),
            'is_completed' => false,
        ]);

        $response = $this->actingAs($user)->get(route('reports.index', [
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-31',
        ]));

        $response->assertOk();
        $response->assertSee('Benim görev');
        $response->assertDontSee('Başkasının görevi');
        $response->assertSee('Görev (toplam)', false);
    }

    public function test_csv_download_contains_utf8_bom_and_row(): void
    {
        $user = User::factory()->create();
        Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'CSV satırı',
            'due_date' => Carbon::parse('2026-06-01 10:00:00'),
        ]);

        $response = $this->actingAs($user)->get(route('reports.export.csv', [
            'date_from' => '2026-06-01',
            'date_to' => '2026-06-30',
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $raw = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $raw);
        $this->assertStringContainsString('CSV satırı', $raw);
    }

    public function test_printable_html_is_attachment(): void
    {
        $user = User::factory()->create();
        Event::create([
            'user_id' => $user->id,
            'title' => 'Yıllık',
            'description' => null,
            'start_date' => Carbon::parse('2026-07-01 09:00:00'),
            'end_date' => Carbon::parse('2026-07-01 10:00:00'),
            'location' => null,
            'all_day' => false,
        ]);

        $response = $this->actingAs($user)->get(route('reports.export.html', [
            'date_from' => '2026-07-01',
            'date_to' => '2026-07-31',
        ]));

        $response->assertOk();
        $this->assertStringContainsString('attachment', strtolower($response->headers->get('Content-Disposition')));
        $this->assertStringContainsString('Yıllık', $response->getContent());
    }

    public function test_rejects_range_over_366_days(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('reports.index', [
            'date_from' => '2025-01-01',
            'date_to' => '2026-12-31',
        ]));

        $response->assertSessionHasErrors('date_to');
    }
}
