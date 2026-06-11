<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('pengguna') || Schema::hasColumn('pengguna', 'remember_token')) {
            return;
        }

        Schema::table('pengguna', function (Blueprint $table): void {
            $table->rememberToken();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('pengguna') || !Schema::hasColumn('pengguna', 'remember_token')) {
            return;
        }

        Schema::table('pengguna', function (Blueprint $table): void {
            $table->dropColumn('remember_token');
        });
    }
};
