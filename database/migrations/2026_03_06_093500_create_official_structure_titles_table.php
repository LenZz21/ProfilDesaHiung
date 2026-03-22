<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('official_structure_titles', function (Blueprint $table) {
            $table->id();
            $table->string('leader_title')->default('KEPALA DESA');
            $table->string('secretary_title')->default('SEKRETARIS DESA');
            $table->string('section_heads_title')->default('KEPALA SEKSI');
            $table->string('kaur_title')->default('KAUR');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('official_structure_titles');
    }
};

