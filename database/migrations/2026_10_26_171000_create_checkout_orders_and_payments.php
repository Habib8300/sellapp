<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('customization_enabled')->default(false);
            $table->decimal('customization_price', 10, 2)->default(0);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 32)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_access_token_hash', 64)->nullable();
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone', 30)->index();
            $table->string('fulfillment_method', 20);
            $table->foreignId('division_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('upazila_id')->nullable()->constrained()->nullOnDelete();
            $table->string('division_name')->nullable();
            $table->string('district_name')->nullable();
            $table->string('upazila_name')->nullable();
            $table->string('delivery_address')->nullable();
            $table->string('pickup_point')->nullable();
            $table->text('order_note')->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->string('payment_method', 30);
            $table->string('payment_status', 30)->default('pending')->index();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->timestamps();
            $table->index(['created_at', 'status']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->string('product_sku');
            $table->string('size', 20);
            $table->string('color', 60);
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('line_total', 10, 2);
            $table->json('customization')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 30);
            $table->string('status', 30)->default('pending')->index();
            $table->decimal('amount', 10, 2);
            $table->string('transaction_reference')->nullable()->index();
            $table->json('provider_data')->nullable();
            $table->timestamps();
            $table->index(['order_id', 'created_at']);
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 30);
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['order_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['customization_enabled', 'customization_price']);
        });
    }
};
