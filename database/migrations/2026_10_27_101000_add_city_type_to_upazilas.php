<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('upazilas', function (Blueprint $table) {
            $table->string('location_type', 20)->default('upazila')->after('name_bn');
            $table->index(['district_id', 'location_type']);
        });
    }

    public function down(): void
    {
        Schema::table('upazilas', function (Blueprint $table) {
            $table->dropIndex(['district_id', 'location_type']);
            $table->dropColumn('location_type');
        });
    }
};
