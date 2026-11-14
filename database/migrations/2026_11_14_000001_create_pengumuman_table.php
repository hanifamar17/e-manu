<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengumuman', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('deskripsi');   // HTML dari editor Quill
            $table->timestamps();            // created_at, updated_at
            $table->index('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengumuman');
    }
};
