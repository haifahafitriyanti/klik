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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('listings')->restrictOnDelete();
            $table->foreignId('reporter_id')->constrained('users')->restrictOnDelete();
            $table->enum('alasan', ['tidak_tersedia', 'tidak_sesuai', 'mencurigakan']);
            $table->text('catatan')->nullable();
            $table->enum('status', ['menunggu', 'ditinjau', 'selesai'])->default('menunggu');
            $table->timestamp('created_at')->useCurrent();

            $table->unsignedBigInteger('active_reporter_id')
                ->nullable()
                ->storedAs("CASE WHEN status IN ('menunggu', 'ditinjau') THEN reporter_id ELSE NULL END");

            $table->unique(['listing_id', 'active_reporter_id'], 'uq_reports_one_active_per_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
