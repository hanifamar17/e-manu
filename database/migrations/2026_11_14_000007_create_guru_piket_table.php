<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guru_piket', function (Blueprint $table) {
            $table->id();
            $table->string('hari', 10);
            $table->string('nama');
            $table->timestamps();
            $table->index('hari');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_piket');
    }
};
