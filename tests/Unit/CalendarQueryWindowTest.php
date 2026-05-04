<?php

namespace Tests\Unit;

use App\Support\CalendarQueryWindow;
use PHPUnit\Framework\TestCase;

class CalendarQueryWindowTest extends TestCase
{
    public function test_returns_null_when_incomplete(): void
    {
        $this->assertNull(CalendarQueryWindow::fromRequest(null, '2026-06-01'));
        $this->assertNull(CalendarQueryWindow::fromRequest('', '2026-06-01'));
    }

    public function test_swaps_inverted_range(): void
    {
        $w = CalendarQueryWindow::fromRequest('2026-06-10', '2026-06-01');
        $this->assertNotNull($w);
        $this->assertTrue($w['start']->lte($w['end']));
        $this->assertSame('2026-06-01', $w['start']->toDateString());
    }

    public function test_clamps_span_to_max_days(): void
    {
        $w = CalendarQueryWindow::fromRequest('2026-01-01', '2026-12-31');
        $this->assertNotNull($w);
        $days = $w['start']->diffInDays($w['end']->copy()->startOfDay());
        $this->assertLessThanOrEqual(CalendarQueryWindow::MAX_SPAN_DAYS, $days);
    }
}
