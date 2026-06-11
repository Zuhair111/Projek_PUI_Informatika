<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('karyawan')) {
            Schema::create('karyawan', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('pengguna_id')->unique();
                $table->string('nip', 30)->unique();
                $table->string('departemen', 100)->nullable();
                $table->string('no_hp', 25)->nullable();
                $table->string('jabatan', 100)->nullable();
                $table->text('foto_url')->nullable();
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('updated_at')->useCurrent();

                $table->foreign('pengguna_id')
                    ->references('id')
                    ->on('pengguna')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('administrator')) {
            Schema::create('administrator', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('pengguna_id')->unique();
                $table->string('level_akses', 50)->default('admin');
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('updated_at')->useCurrent();

                $table->foreign('pengguna_id')
                    ->references('id')
                    ->on('pengguna')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('administrator');
        Schema::dropIfExists('karyawan');
    }
};
