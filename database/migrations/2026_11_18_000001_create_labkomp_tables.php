<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // stok = stok TERSEDIA (berkurang saat dipinjam, bertambah saat dikembalikan), sama seperti versi Flask.
        Schema::create('labkomp_items', function (Blueprint $t) {
            $t->id();
            $t->string('kode', 20)->unique();
            $t->string('nama_barang');
            $t->unsignedInteger('stok')->default(0);
            $t->string('satuan', 20);
            $t->string('kondisi', 20)->default('Baik');
            $t->string('lokasi')->nullable();
            $t->text('catatan')->nullable();
            $t->timestamps();
        });

        Schema::create('labkomp_peminjaman', function (Blueprint $t) {
            $t->id();
            $t->uuid('uuid')->unique();          // link publik bukti peminjaman
            $t->string('kode', 20)->unique();    // kode referensi (dipakai saat pengembalian)
            $t->string('nama_peminjam');
            $t->string('email_peminjam');
            $t->date('tgl_pinjam');
            $t->date('tgl_kembali');
            $t->string('status', 20)->default('dipinjam')->index();
            $t->text('catatan')->nullable();
            $t->string('bukti_kembali')->nullable();
            $t->timestamp('dikembalikan_at')->nullable();
            $t->timestamps();
        });

        Schema::create('labkomp_peminjaman_item', function (Blueprint $t) {
            $t->id();
            $t->foreignId('peminjaman_id')->constrained('labkomp_peminjaman')->cascadeOnDelete();
            $t->foreignId('item_id')->nullable()->constrained('labkomp_items')->nullOnDelete();
            $t->string('kode_barang', 20)->nullable();   // snapshot, riwayat tetap utuh walau barang dihapus
            $t->string('nama_barang');
            $t->unsignedInteger('qty');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('labkomp_peminjaman_item');
        Schema::dropIfExists('labkomp_peminjaman');
        Schema::dropIfExists('labkomp_items');
    }
};
