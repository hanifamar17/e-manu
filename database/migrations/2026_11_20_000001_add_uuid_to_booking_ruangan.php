<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('booking_ruangan', 'uuid')) {
            Schema::table('booking_ruangan', fn (Blueprint $t) => $t->uuid('uuid')->nullable()->after('id'));
        }

        // pengajuan lama ikut dapat uuid
        DB::table('booking_ruangan')->whereNull('uuid')->orderBy('id')->each(
            fn ($r) => DB::table('booking_ruangan')->where('id', $r->id)->update(['uuid' => (string) Str::uuid()])
        );

        Schema::table('booking_ruangan', fn (Blueprint $t) => $t->unique('uuid'));
    }

    public function down(): void
    {
        Schema::table('booking_ruangan', function (Blueprint $t) {
            $t->dropUnique(['uuid']);
            $t->dropColumn('uuid');
        });
    }
};
