<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peminjaman_item', function (Blueprint $t) {
            $t->id();
            $t->foreignId('peminjaman_id')->constrained('peminjaman')->cascadeOnDelete();
            // nullOnDelete: riwayat tetap utuh walau barang inventaris dihapus (nama/merk disalin sebagai snapshot)
            $t->foreignId('inventaris_id')->nullable()->constrained('inventaris')->nullOnDelete();
            $t->unsignedSmallInteger('no');
            $t->string('nama_barang', 150);
            $t->string('merk', 100)->nullable();
            $t->unsignedInteger('jumlah');
            $t->string('kondisi', 20)->default('Baik');
            $t->boolean('dikembalikan')->default(false);
            $t->timestamp('tgl_dikembalikan')->nullable();
            $t->string('catatan_kembali', 255)->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjaman_item');
    }
};
