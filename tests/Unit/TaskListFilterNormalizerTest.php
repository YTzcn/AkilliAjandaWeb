<?php

namespace Tests\Unit;

use App\Support\TaskListFilterNormalizer;
use PHPUnit\Framework\TestCase;

class TaskListFilterNormalizerTest extends TestCase
{
    public function test_expands_due_date_when_range_empty(): void
    {
        $out = TaskListFilterNormalizer::normalize([
            'due_date' => '2026-03-20',
        ]);

        $this->assertSame('2026-03-20', $out['due_from']);
        $this->assertSame('2026-03-20', $out['due_to']);
        $this->assertArrayNotHasKey('due_date', $out);
        $this->assertSame('due_date', $out['sort']);
        $this->assertSame('asc', $out['dir']);
    }

    public function test_does_not_override_existing_range_with_due_date(): void
    {
        $out = TaskListFilterNormalizer::normalize([
            'due_date' => '2026-03-01',
            'due_from' => '2026-03-10',
            'due_to' => '2026-03-15',
        ]);

        $this->assertSame('2026-03-10', $out['due_from']);
        $this->assertSame('2026-03-15', $out['due_to']);
        $this->assertArrayNotHasKey('due_date', $out);
    }

    public function test_invalid_sort_falls_back(): void
    {
        $out = TaskListFilterNormalizer::normalize(['sort' => 'injected', 'dir' => 'DESC']);

        $this->assertSame('due_date', $out['sort']);
        $this->assertSame('desc', $out['dir']);
    }

    public function test_casts_priority_to_int(): void
    {
        $out = TaskListFilterNormalizer::normalize(['priority' => '3']);

        $this->assertSame(3, $out['priority']);
    }

    public function test_passes_text_search_query(): void
    {
        $out = TaskListFilterNormalizer::normalize(['q' => '  toplantı  ']);

        $this->assertSame('  toplantı  ', $out['q']);
    }
}
