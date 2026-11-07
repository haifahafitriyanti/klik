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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('plan_id')->constrained('plans')->restrictOnDelete();
            $table->foreignId('lapak_id')->nullable()->unique('uq_transactions_lapak_id')->constrained('lapaks')->nullOnDelete();
            $table->string('order_id', 50)->unique('uq_transactions_order_id');
            $table->unsignedInteger('jumlah');
            $table->unsignedInteger('durasi_hari');
            $table->enum('status', ['pending', 'paid', 'expire', 'cancel', 'failed'])->default('pending');
            $table->string('payment_type', 30)->nullable();
            $table->json('form_payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('gateway_status', 30)->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('tc_accepted_at');
            $table->boolean('perlu_tinjauan')->default(false);
            $table->text('review_reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['status', 'expires_at'], 'idx_transactions_status_expires');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
