<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perizinan', function (Blueprint $table) {
            $table->id();
            $table->string('id_perizinan', 24)->unique(); // PZ-YYYYMMDD-XXXX
            $table->uuid('uuid')->unique();               // link surat publik

            // snapshot siswa saat izin dibuat (tanpa FK: master siswa boleh diimpor ulang)
            $table->string('nisn', 20)->index();
            $table->string('nama_siswa', 100);
            $table->string('kelas_umum', 30);
            $table->string('wali_kelas_tujuan', 100)->nullable();

            $table->date('tanggal_mulai')->index();
            $table->time('jam_mulai');
            $table->date('tanggal_selesai');
            $table->time('jam_selesai');
            $table->string('durasi', 30); // label: "2 JPL", "1 Hari", ...
            $table->string('alasan', 200);

            $table->string('petugas', 100);
            $table->string('status_petugas', 20); // guru | wali_kelas | pembimbing
            $table->boolean('need_acc')->default(false);
            $table->string('status', 20)->index(); // menunggu_acc | aktif | disetujui | ditolak | dibatalkan

            // proses ACC
            $table->string('diproses_oleh', 100)->nullable();
            $table->dateTime('tanggal_acc')->nullable();
            $table->string('catatan_wali_kelas', 200)->nullable();

            // pembatalan
            $table->string('dibatalkan_oleh', 100)->nullable();
            $table->string('alasan_batal', 200)->nullable();
            $table->dateTime('dibatalkan_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perizinan');
    }
};
