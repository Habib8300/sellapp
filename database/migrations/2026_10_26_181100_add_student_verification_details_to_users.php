<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('student_institution', 160)->nullable();
            $table->string('student_email')->nullable();
            $table->timestamp('student_verification_submitted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'student_institution',
                'student_email',
                'student_verification_submitted_at',
            ]);
        });
    }
};
