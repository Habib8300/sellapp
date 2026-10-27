<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('upazila_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 160);
            $table->string('name_bn', 160)->nullable();
            $table->string('slug')->unique();
            $table->string('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'name']);
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->foreignId('campus_id')->nullable()->after('upazila_id')->constrained()->nullOnDelete();
            $table->string('locality', 180)->nullable()->after('campus_id');
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('campus_id');
            $table->dropColumn('locality');
        });

        Schema::dropIfExists('campuses');
    }
};
