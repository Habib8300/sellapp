<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_hero_slides', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 180);
            $table->text('subtitle')->nullable();
            $table->string('image_path')->nullable();
            $table->string('button_text', 80)->default('জার্সি দেখুন');
            $table->string('button_url', 255)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_hero_slides');
    }
};
