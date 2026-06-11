<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'status_presensi') THEN CREATE TYPE status_presensi AS ENUM ('hadir', 'terlambat', 'izin', 'alpha', 'ditolak'); END IF; END $$;");
        DB::statement("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'jenis_log_deteksi') THEN CREATE TYPE jenis_log_deteksi AS ENUM ('developer_options', 'mock_app_terdeteksi', 'koordinat_tidak_konsisten', 'aman'); END IF; END $$;");

        if (!Schema::hasTable('presensi')) {
            Schema::create('presensi', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('karyawan_id');
                $table->uuid('geofencing_id');
                $table->date('tanggal');
                $table->timestampTz('check_in_at')->nullable();
                $table->timestampTz('check_out_at')->nullable();
                $table->decimal('lat_check_in', 10, 7)->nullable();
                $table->decimal('lng_check_in', 10, 7)->nullable();
                $table->decimal('lat_check_out', 10, 7)->nullable();
                $table->decimal('lng_check_out', 10, 7)->nullable();
                $table->decimal('jarak_dari_titik_meter', 8, 2)->nullable();
                $table->string('sumber_lokasi', 20)->default('gps');
                $table->string('device_id', 120)->nullable();
                $table->text('catatan')->nullable();
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('updated_at')->useCurrent();

                $table->foreign('karyawan_id')->references('id')->on('karyawan')->cascadeOnUpdate()->restrictOnDelete();
                $table->foreign('geofencing_id')->references('id')->on('geofencing')->cascadeOnUpdate()->restrictOnDelete();
                $table->unique(['karyawan_id', 'tanggal'], 'uq_presensi_harian');
            });

            DB::statement("ALTER TABLE presensi ADD COLUMN status status_presensi NOT NULL DEFAULT 'hadir';");
        }

        if (!Schema::hasTable('log_deteksi')) {
            Schema::create('log_deteksi', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('presensi_id')->nullable();
                $table->uuid('karyawan_id');
                $table->boolean('developer_options_on')->default(false);
                $table->boolean('aplikasi_mock_terdeteksi')->default(false);
                $table->boolean('konsistensi_koordinat_valid')->default(true);
                $table->jsonb('detail_deteksi')->nullable();
                $table->timestampTz('detected_at')->useCurrent();
                $table->timestampTz('created_at')->useCurrent();

                $table->foreign('presensi_id')->references('id')->on('presensi')->cascadeOnUpdate()->nullOnDelete();
                $table->foreign('karyawan_id')->references('id')->on('karyawan')->cascadeOnUpdate()->cascadeOnDelete();
            });

            DB::statement("ALTER TABLE log_deteksi ADD COLUMN jenis_deteksi jenis_log_deteksi NOT NULL DEFAULT 'aman';");
        }

        if (Schema::hasTable('presensi')) {
            DB::statement('CREATE INDEX IF NOT EXISTS idx_presensi_tanggal ON presensi (tanggal);');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_presensi_karyawan ON presensi (karyawan_id);');
        }

        if (Schema::hasTable('log_deteksi')) {
            DB::statement('CREATE INDEX IF NOT EXISTS idx_logdeteksi_karyawan ON log_deteksi (karyawan_id);');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_logdeteksi_detectedat ON log_deteksi (detected_at);');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('log_deteksi');
        Schema::dropIfExists('presensi');
        DB::statement('DROP TYPE IF EXISTS jenis_log_deteksi;');
        DB::statement('DROP TYPE IF EXISTS status_presensi;');
    }
};
