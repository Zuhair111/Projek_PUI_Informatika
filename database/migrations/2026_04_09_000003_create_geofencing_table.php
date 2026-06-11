<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('geofencing')) {
            Schema::create('geofencing', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('nama_lokasi', 120);
                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);
                $table->integer('radius_meter');
                $table->boolean('aktif')->default(true);
                $table->uuid('dibuat_oleh_admin_id')->nullable();
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('updated_at')->useCurrent();

                $table->foreign('dibuat_oleh_admin_id')
                    ->references('id')
                    ->on('administrator')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('geofencing');
    }
};
