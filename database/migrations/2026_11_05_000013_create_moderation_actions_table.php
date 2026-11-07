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
        Schema::create('moderation_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('listings')->restrictOnDelete();
            $table->foreignId('admin_id')->constrained('users')->restrictOnDelete();
            $table->enum('kode_alasan', ['M1', 'M2', 'M3', 'M4', 'M5', 'M6', 'M7', 'M8']);
            $table->text('alasan');
            $table->timestamp('hidden_at');
            $table->timestamp('response_deadline');
            $table->enum('status', ['menunggu', 'banding', 'pulih', 'hangus', 'tanpa_banding'])->default('menunggu');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'response_deadline'], 'idx_moderation_status_deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moderation_actions');
    }
};
