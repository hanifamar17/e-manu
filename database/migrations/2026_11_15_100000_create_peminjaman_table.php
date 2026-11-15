<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peminjaman', function (Blueprint $t) {
            $t->id();
            $t->uuid('record_id')->unique();
            $t->string('nomor_surat', 40)->unique();   // 001/PA/MANU/IX/2026
            $t->string('nama', 150);
            $t->string('jenis_kelamin', 10);
            $t->string('pekerjaan', 150);
            $t->string('alamat', 255);
            $t->string('telepon', 30);
            $t->string('no_identitas', 50);
            $t->string('tujuan', 255);
            $t->string('lokasi_penggunaan', 150);
            $t->date('tgl_pinjam');
            $t->date('tgl_kembali');
            $t->string('kota', 80)->default('Yogyakarta');
            $t->date('tgl_surat');
            $t->string('nama_petugas', 150);
            $t->boolean('jaminan_identitas')->default(false);
            $t->string('jenis_identitas_jaminan', 10)->nullable();
            $t->boolean('jaminan_surat')->default(false);
            $t->string('pdf_path')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjaman');
    }
};
