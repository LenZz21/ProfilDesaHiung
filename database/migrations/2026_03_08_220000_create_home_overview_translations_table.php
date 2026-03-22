<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_overview_translations', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 10)->unique();
            $table->longText('about')->nullable();
            $table->timestamps();
        });

        $existingAbout = DB::table('village_profiles')->value('about');

        DB::table('home_overview_translations')->insert([
            'locale' => 'en',
            'about' => filled($existingAbout) ? (string) $existingAbout : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('home_overview_translations');
    }
};
