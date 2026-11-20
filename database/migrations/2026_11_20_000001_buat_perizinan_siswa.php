<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perizinan_siswa', function (Blueprint $t) {
            $t->id();
            $t->foreignId('perizinan_id')->constrained('perizinan')->cascadeOnDelete();
            // snapshot siswa saat izin dibuat (tanpa FK ke siswa)
            $t->string('nisn', 20)->index();
            $t->string('nama_siswa', 100);
            $t->string('kelas_umum', 30);
            $t->timestamps();
            $t->unique(['perizinan_id', 'nisn']);
        });

        // izin lama (1 siswa) -> 1 baris pivot
        DB::statement('INSERT INTO perizinan_siswa (perizinan_id, nisn, nama_siswa, kelas_umum, created_at, updated_at)
                       SELECT id, nisn, nama_siswa, kelas_umum, NOW(), NOW() FROM perizinan');

        Schema::table('perizinan', fn (Blueprint $t) => $t->dropIndex(['nisn']));
        Schema::table('perizinan', fn (Blueprint $t) => $t->dropColumn(['nisn', 'nama_siswa', 'kelas_umum']));
    }

    public function down(): void
    {
        Schema::table('perizinan', function (Blueprint $t) {
            $t->string('nisn', 20)->nullable()->index();
            $t->string('nama_siswa', 100)->nullable();
            $t->string('kelas_umum', 30)->nullable();
        });

        DB::statement('UPDATE perizinan p
                       JOIN (SELECT perizinan_id, MIN(id) AS mid FROM perizinan_siswa GROUP BY perizinan_id) m ON m.perizinan_id = p.id
                       JOIN perizinan_siswa s ON s.id = m.mid
                       SET p.nisn = s.nisn, p.nama_siswa = s.nama_siswa, p.kelas_umum = s.kelas_umum');

        Schema::dropIfExists('perizinan_siswa');
    }
};
