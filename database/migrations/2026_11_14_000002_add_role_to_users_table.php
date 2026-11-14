<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Aman bila kolom role sudah ada dari langkah sebelumnya.
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role', 30)->default('')->after('email');
            });
        }
    }

    public function down(): void
    {
        // sengaja kosong: kolom role mungkin dibuat di luar migration ini
    }
};
