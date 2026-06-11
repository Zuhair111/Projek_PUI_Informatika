<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement(<<<'SQL'
DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM pg_type WHERE typname = 'jenis_log_deteksi') THEN
        BEGIN
            ALTER TYPE jenis_log_deteksi ADD VALUE IF NOT EXISTS 'safe_device_signal';
        EXCEPTION
            WHEN duplicate_object THEN NULL;
        END;
    END IF;
END $$;
SQL);
    }

    public function down(): void
    {
        // PostgreSQL enum values cannot be removed safely in-place.
    }
};
