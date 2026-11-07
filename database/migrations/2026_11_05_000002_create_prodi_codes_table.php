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
        Schema::create('prodi_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jurusan_id')->constrained('jurusans')->restrictOnDelete();
            $table->char('kode', 4);
            $table->enum('jenjang', ['D3', 'D4']);
            $table->string('nama_prodi', 100);
            $table->unsignedSmallInteger('berlaku_dari')->nullable();
            $table->unsignedSmallInteger('berlaku_sampai')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prodi_codes');
    }
};
