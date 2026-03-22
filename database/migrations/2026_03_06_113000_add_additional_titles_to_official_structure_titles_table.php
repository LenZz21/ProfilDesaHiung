<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('official_structure_titles', function (Blueprint $table) {
            $table->json('additional_titles')->nullable()->after('head_lindongang_title');
        });
    }

    public function down(): void
    {
        Schema::table('official_structure_titles', function (Blueprint $table) {
            $table->dropColumn('additional_titles');
        });
    }
};

