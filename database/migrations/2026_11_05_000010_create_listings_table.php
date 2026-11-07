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
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lapak_id')->constrained('lapaks')->restrictOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignId('brand_id')->constrained('brands')->restrictOnDelete();
            $table->string('judul', 150);
            $table->string('model', 100);
            $table->string('prosesor', 100);
            $table->unsignedSmallInteger('ram_gb');
            $table->string('penyimpanan', 50);
            $table->boolean('gpu_dedicated')->default(false);
            $table->boolean('tipe_2in1')->default(false);
            $table->decimal('berat_kg', 3, 2)->nullable();
            $table->unsignedInteger('harga');
            $table->string('kondisi', 50);
            $table->text('deskripsi');
            $table->string('no_wa', 20);
            $table->enum('status', ['aktif', 'tersembunyi', 'terjual', 'kedaluwarsa'])->default('aktif');
            $table->timestamp('sold_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('terakhir_dikonfirmasi')->nullable();

            $table->unsignedBigInteger('active_lapak_id')
                ->nullable()
                ->storedAs("CASE WHEN status = 'aktif' THEN lapak_id ELSE NULL END");

            $table->unique('active_lapak_id', 'uq_listings_active_lapak_id');
            $table->index(['status', 'category_id', 'brand_id', 'harga'], 'idx_listings_catalog');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
