<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('kelas', 'user_id')) {
            Schema::table('kelas', function (Blueprint $t) {
                $t->foreignId('user_id')->nullable()->after('no_hp')->constrained('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $t) {
            $t->dropConstrainedForeignId('user_id');
        });
    }
};