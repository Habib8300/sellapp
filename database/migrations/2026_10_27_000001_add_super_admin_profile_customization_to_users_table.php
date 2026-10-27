<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('profile_photo_path')->nullable()->after('email');
            $table->string('admin_title')->nullable()->after('profile_photo_path');
            $table->text('admin_bio')->nullable()->after('admin_title');
            $table->string('phone')->nullable()->after('admin_bio');
            $table->string('timezone')->default('Asia/Dhaka')->after('phone');
            $table->string('theme')->default('default')->after('timezone');
            $table->string('preferred_language')->default('bn')->after('theme');
            $table->text('signature')->nullable()->after('preferred_language');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'profile_photo_path',
                'admin_title',
                'admin_bio',
                'phone',
                'timezone',
                'theme',
                'preferred_language',
                'signature',
            ]);
        });
    }
};
