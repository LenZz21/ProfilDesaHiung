<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('village_profiles', function (Blueprint $table) {
            $table->string('home_background_image_1')->nullable();
            $table->string('home_background_image_2')->nullable();
            $table->string('home_background_image_3')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('village_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'home_background_image_1',
                'home_background_image_2',
                'home_background_image_3',
            ]);
        });
    }
};
