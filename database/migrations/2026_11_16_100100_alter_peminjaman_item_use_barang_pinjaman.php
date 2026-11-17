<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peminjaman_item', function (Blueprint $t) {
            $t->dropConstrainedForeignId('inventaris_id');
            $t->foreignId('barang_pinjaman_id')->nullable()->after('peminjaman_id')
                ->constrained('barang_pinjaman')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('peminjaman_item', function (Blueprint $t) {
            $t->dropConstrainedForeignId('barang_pinjaman_id');
            $t->foreignId('inventaris_id')->nullable()->after('peminjaman_id')
                ->constrained('inventaris')->nullOnDelete();
        });
    }
};
