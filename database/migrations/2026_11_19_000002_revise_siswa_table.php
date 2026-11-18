<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('siswa')) {
            Schema::create('siswa', function (Blueprint $t) {
                $t->id();
                $t->string('nisn', 15)->unique();
                $t->string('nama_siswa');
                $t->char('jenis_kelamin', 1);
                $t->string('kelas_umum', 30)->index();
                $t->string('kelas_diniyyah', 30)->nullable();
                $t->unsignedSmallInteger('tahun_masuk')->nullable();
                $t->timestamps();
            });

            return;
        }

        Schema::table('siswa', function (Blueprint $t) {
            if (! Schema::hasColumn('siswa', 'kelas_diniyyah')) {
                $t->string('kelas_diniyyah', 30)->nullable()->after('kelas_umum');
            }
            if (! Schema::hasColumn('siswa', 'tahun_masuk')) {
                $t->unsignedSmallInteger('tahun_masuk')->nullable()->after('kelas_diniyyah');
            }
        });
    }

    public function down(): void
    {
        Schema::table('siswa', fn (Blueprint $t) => $t->dropColumn(['kelas_diniyyah', 'tahun_masuk']));
    }
};
