<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'role_pengguna') THEN CREATE TYPE role_pengguna AS ENUM ('karyawan', 'administrator'); END IF; END $$;");

        Schema::create('pengguna', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('nama', 120);
            $table->string('email', 150)->unique();
            $table->text('password_hash');
            $table->rememberToken();
            $table->boolean('is_active')->default(true);
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
        });

        DB::statement("ALTER TABLE pengguna ADD COLUMN role role_pengguna NOT NULL DEFAULT 'karyawan';");
    }

    public function down(): void
    {
        Schema::dropIfExists('pengguna');
        DB::statement('DROP TYPE IF EXISTS role_pengguna;');
    }
};
