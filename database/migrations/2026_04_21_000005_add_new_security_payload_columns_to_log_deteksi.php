<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('log_deteksi')) {
            return;
        }

        Schema::table('log_deteksi', function (Blueprint $table): void {
            if (!Schema::hasColumn('log_deteksi', 'mock_location')) {
                $table->boolean('mock_location')->default(false);
            }

            if (!Schema::hasColumn('log_deteksi', 'is_real_device')) {
                $table->boolean('is_real_device')->default(true);
            }

            if (!Schema::hasColumn('log_deteksi', 'is_rooted')) {
                $table->boolean('is_rooted')->default(false);
            }

            if (!Schema::hasColumn('log_deteksi', 'is_dev_mode')) {
                $table->boolean('is_dev_mode')->default(false);
            }

            if (!Schema::hasColumn('log_deteksi', 'final_status')) {
                $table->string('final_status', 64)->default('SAFE');
            }

            if (!Schema::hasColumn('log_deteksi', 'message')) {
                $table->text('message')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('log_deteksi')) {
            return;
        }

        Schema::table('log_deteksi', function (Blueprint $table): void {
            if (Schema::hasColumn('log_deteksi', 'message')) {
                $table->dropColumn('message');
            }

            if (Schema::hasColumn('log_deteksi', 'final_status')) {
                $table->dropColumn('final_status');
            }

            if (Schema::hasColumn('log_deteksi', 'is_dev_mode')) {
                $table->dropColumn('is_dev_mode');
            }

            if (Schema::hasColumn('log_deteksi', 'is_rooted')) {
                $table->dropColumn('is_rooted');
            }

            if (Schema::hasColumn('log_deteksi', 'is_real_device')) {
                $table->dropColumn('is_real_device');
            }

            if (Schema::hasColumn('log_deteksi', 'mock_location')) {
                $table->dropColumn('mock_location');
            }
        });
    }
};
