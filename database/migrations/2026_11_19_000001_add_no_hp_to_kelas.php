<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('kelas', 'no_hp')) {
            Schema::table('kelas', fn (Blueprint $t) => $t->string('no_hp', 20)->nullable()->after('wali_kelas'));
        }
    }

    public function down(): void
    {
        Schema::table('kelas', fn (Blueprint $t) => $t->dropColumn('no_hp'));
    }
};
