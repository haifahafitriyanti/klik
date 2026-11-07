<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lapaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('plan_id')->constrained('plans')->restrictOnDelete();
            $table->enum('status', ['aktif', 'jeda', 'habis', 'ditutup'])->default('aktif');
            $table->unsignedInteger('harga_saat_beli');
            $table->unsignedInteger('durasi_hari');
            $table->timestamp('tc_accepted_at');
            $table->timestamp('started_at');
            $table->timestamp('expires_at');
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'expires_at'], 'idx_lapaks_status_expires');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lapaks');
    }
};
