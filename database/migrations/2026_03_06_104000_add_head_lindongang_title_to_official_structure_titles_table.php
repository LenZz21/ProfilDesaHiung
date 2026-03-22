<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('official_structure_titles', function (Blueprint $table) {
            $table->string('head_lindongang_title')->default('KEPALA LINDONGANG')->after('kaur_title');
        });
    }

    public function down(): void
    {
        Schema::table('official_structure_titles', function (Blueprint $table) {
            $table->dropColumn('head_lindongang_title');
        });
    }
};

