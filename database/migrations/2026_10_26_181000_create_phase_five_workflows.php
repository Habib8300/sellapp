<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('student_verified')->default(false);
        });

        Schema::create('bulk_discount_slabs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('minimum_quantity')->unique();
            $table->decimal('discount_percent', 5, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        DB::table('bulk_discount_slabs')->insert([
            ['minimum_quantity' => 10, 'discount_percent' => 5, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['minimum_quantity' => 25, 'discount_percent' => 10, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('type', 20);
            $table->decimal('amount', 10, 2);
            $table->decimal('minimum_order', 10, 2)->default(0);
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('student_only')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->string('slug', 180)->unique();
            $table->string('department', 160)->nullable();
            $table->string('batch', 80)->nullable();
            $table->text('description')->nullable();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('deadline');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('campaign_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('participant_name');
            $table->string('participant_email')->nullable();
            $table->unsignedInteger('quantity');
            $table->timestamps();
            $table->unique(['campaign_id', 'order_id']);
        });

        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 30)->index();
            $table->string('email')->nullable();
            $table->string('department', 160)->nullable();
            $table->string('batch', 80)->nullable();
            $table->unsignedInteger('quantity');
            $table->text('details')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('coupon_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->foreignId('campaign_id')->nullable()->after('coupon_id')->constrained()->nullOnDelete();
            $table->decimal('discount_amount', 10, 2)->default(0)->after('subtotal');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('campaign_id');
            $table->dropConstrainedForeignId('coupon_id');
            $table->dropColumn('discount_amount');
        });

        Schema::dropIfExists('quote_requests');
        Schema::dropIfExists('campaign_orders');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('bulk_discount_slabs');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('student_verified');
        });
    }
};
