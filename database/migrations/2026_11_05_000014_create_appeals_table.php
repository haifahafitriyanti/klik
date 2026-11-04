<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appeals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moderation_action_id')->constrained('moderation_actions')->restrictOnDelete();
            $table->unsignedTinyInteger('nomor_banding');
            $table->text('alasan');
            $table->string('bukti_path', 255)->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->enum('status', ['menunggu', 'diterima', 'ditolak'])->default('menunggu');
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan_admin')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamp('batas_banding_berikutnya')->nullable();
            $table->timestamps();

            $table->unique(['moderation_action_id', 'nomor_banding'], 'uq_appeals_action_nomor');
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE appeals ADD CONSTRAINT chk_appeals_nomor_banding CHECK (nomor_banding BETWEEN 1 AND 3)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE appeals DROP CONSTRAINT IF EXISTS chk_appeals_nomor_banding');
        }

        Schema::dropIfExists('appeals');
    }
};

