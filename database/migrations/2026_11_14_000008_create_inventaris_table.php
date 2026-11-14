<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventaris', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();      // INV-0001 (dari sheet lama)
            $table->string('nama_barang', 150);
            $table->string('merk', 100)->nullable();
            $table->string('kondisi', 20)->default('Baik');
            $table->string('lokasi', 100)->nullable();
            $table->unsignedSmallInteger('tahun_pengadaan')->nullable();
            $table->string('sumber_dana', 20)->nullable();
            $table->string('sub_bidang', 30)->nullable()->index();
            $table->unsignedInteger('jumlah_total')->default(0);
            $table->unsignedInteger('jumlah_dipinjam')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventaris');
    }
};