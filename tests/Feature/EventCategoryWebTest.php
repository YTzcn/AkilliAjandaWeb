<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventCategoryWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_can_be_created_with_categories_via_web(): void
    {
        $user = User::factory()->create();
        $cat = Category::create([
            'user_id' => $user->id,
            'name' => 'İş',
            'description' => null,
            'color' => '#4158d0',
        ]);

        $start = Carbon::parse('2026-04-01 10:00:00');
        $end = Carbon::parse('2026-04-01 11:00:00');

        $response = $this->actingAs($user)->post(route('events.store'), [
            'title' => 'Planlama',
            'description' => null,
            'start_time' => $start->format('Y-m-d\TH:i'),
            'end_time' => $end->format('Y-m-d\TH:i'),
            'location' => null,
            'category_ids' => [(string) $cat->id],
        ]);

        $response->assertRedirect(route('events.index'));
        $this->assertDatabaseHas('events', ['title' => 'Planlama', 'user_id' => $user->id]);
        $event = Event::query()->where('title', 'Planlama')->first();
        $this->assertNotNull($event);
        $this->assertTrue($event->categories->pluck('id')->contains($cat->id));
    }

    public function test_events_index_filters_by_category(): void
    {
        $user = User::factory()->create();
        $catA = Category::create(['user_id' => $user->id, 'name' => 'A', 'description' => null]);
        $catB = Category::create(['user_id' => $user->id, 'name' => 'B', 'description' => null]);

        $e1 = Event::create([
            'user_id' => $user->id,
            'title' => 'Sadece A',
            'description' => null,
            'start_date' => Carbon::parse('2026-05-01 09:00:00'),
            'end_date' => Carbon::parse('2026-05-01 10:00:00'),
            'location' => null,
            'all_day' => false,
        ]);
        $e1->categories()->sync([$catA->id]);

        $e2 = Event::create([
            'user_id' => $user->id,
            'title' => 'Sadece B',
            'description' => null,
            'start_date' => Carbon::parse('2026-05-02 09:00:00'),
            'end_date' => Carbon::parse('2026-05-02 10:00:00'),
            'location' => null,
            'all_day' => false,
        ]);
        $e2->categories()->sync([$catB->id]);

        $response = $this->actingAs($user)->get(route('events.index', ['category_id' => $catA->id]));

        $response->assertOk();
        $response->assertSee('Sadece A', false);
        $response->assertDontSee('Sadece B', false);
    }

    public function test_category_service_filters_foreign_ids(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $mine = Category::create(['user_id' => $user->id, 'name' => 'Benim', 'description' => null]);
        $theirs = Category::create(['user_id' => $other->id, 'name' => 'Başkasının', 'description' => null]);

        $this->actingAs($user);
        $service = app(\App\Services\CategoryService::class);
        $filtered = $service->filterOwnedCategoryIds([$mine->id, $theirs->id, 99999]);

        $this->assertSame([$mine->id], $filtered);
    }
}
