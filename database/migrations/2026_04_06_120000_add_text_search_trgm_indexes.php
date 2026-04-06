<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * PostgreSQL pg_trgm ile ILIKE aramalarına yardımcı GIN indeksleri (Hafta 4).
 * SQLite / diğer sürücülerde atlanır.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

        DB::statement('CREATE INDEX IF NOT EXISTS idx_tasks_title_trgm ON tasks USING gin (title gin_trgm_ops)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_tasks_description_trgm ON tasks USING gin ((COALESCE(description, \'\')) gin_trgm_ops)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_events_title_trgm ON events USING gin (title gin_trgm_ops)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_events_description_trgm ON events USING gin ((COALESCE(description, \'\')) gin_trgm_ops)');
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        foreach ([
            'idx_events_description_trgm',
            'idx_events_title_trgm',
            'idx_tasks_description_trgm',
            'idx_tasks_title_trgm',
        ] as $name) {
            DB::statement('DROP INDEX IF EXISTS '.$name);
        }
    }
};
