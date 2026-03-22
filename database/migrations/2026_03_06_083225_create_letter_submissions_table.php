<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('letter_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('service_type', 100);
            $table->string('service_name');
            $table->string('full_name');
            $table->string('nik', 25);
            $table->string('whatsapp', 25);
            $table->string('email')->nullable();
            $table->text('purpose');
            $table->string('status', 20)->default('baru')->index();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_submissions');
    }
};
