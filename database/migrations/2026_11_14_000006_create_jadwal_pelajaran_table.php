<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('urutan')->default(0); // = kolom id sheet lama (urutan sesi asli)
            $table->string('hari', 10);                    // SENIN..SABTU
            $table->string('nama_kelas');
            $table->string('sesi')->nullable();
            $table->string('waktu');                       // mis. 07.00-07.45
            $table->string('nama_mapel');
            $table->string('nama_guru')->nullable();
            $table->timestamps();
            $table->index(['hari', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_pelajaran');
    }
};
