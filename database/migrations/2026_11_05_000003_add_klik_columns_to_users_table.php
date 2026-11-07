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
        Schema::table('users', function (Blueprint $table) {
            $table->char('nim', 10)->nullable()->unique('uq_users_nim')->after('id');
            $table->string('username', 50)->nullable()->after('name');
            $table->enum('status_mahasiswa', ['aktif', 'alumni'])->default('aktif')->after('nim');
            $table->char('kode_prodi_nim', 4)->nullable()->after('status_mahasiswa');
            $table->unsignedSmallInteger('angkatan')->nullable()->after('kode_prodi_nim');
            $table->foreignId('prodi_code_id')->nullable()->after('angkatan')->constrained('prodi_codes')->nullOnDelete();
            $table->boolean('kode_prodi_tidak_dikenal')->default(false)->after('prodi_code_id');
            $table->string('ktm_path', 255)->nullable()->after('kode_prodi_tidak_dikenal');
            $table->enum('status_verifikasi', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu')->after('ktm_path');
            $table->foreignId('verified_by')->nullable()->after('status_verifikasi')->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->text('alasan_penolakan')->nullable()->after('verified_at');
            $table->enum('role', ['user', 'admin'])->default('user')->after('alasan_penolakan');
            $table->boolean('is_suspended')->default(false)->after('role');
            $table->string('email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['prodi_code_id']);
            $table->dropForeign(['verified_by']);
            $table->dropUnique('uq_users_nim');
            $table->dropColumn([
                'nim',
                'username',
                'status_mahasiswa',
                'kode_prodi_nim',
                'angkatan',
                'prodi_code_id',
                'kode_prodi_tidak_dikenal',
                'ktm_path',
                'status_verifikasi',
                'verified_by',
                'verified_at',
                'alasan_penolakan',
                'role',
                'is_suspended',
            ]);
            $table->string('email')->nullable(false)->change();
        });
    }
};
