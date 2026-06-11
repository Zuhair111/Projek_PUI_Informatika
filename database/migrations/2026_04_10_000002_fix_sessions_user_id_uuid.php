<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('sessions')) {
            return;
        }

        DB::statement('ALTER TABLE sessions DROP COLUMN IF EXISTS user_id');
        DB::statement('ALTER TABLE sessions ADD COLUMN user_id uuid NULL');
        DB::statement('CREATE INDEX IF NOT EXISTS sessions_user_id_index ON sessions (user_id)');
    }

    public function down(): void
    {
        if (!Schema::hasTable('sessions')) {
            return;
        }

        DB::statement('ALTER TABLE sessions DROP COLUMN IF EXISTS user_id');
        DB::statement('ALTER TABLE sessions ADD COLUMN user_id bigint NULL');
        DB::statement('CREATE INDEX IF NOT EXISTS sessions_user_id_index ON sessions (user_id)');
    }
};
