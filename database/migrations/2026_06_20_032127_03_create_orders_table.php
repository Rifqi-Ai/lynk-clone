<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->string('id', 20)->primary()->comment('e.g. ORD-20260620-XXXXX');
            $table->foreignId('buyer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('buyer_email');

            // Product being purchased
            $table->string('product_id', 12);
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();

            // Snapshot of creator at time of purchase
            $table->foreignId('creator_user_id')->constrained('users')->cascadeOnDelete();

            // Pricing snapshot
            $table->decimal('unit_price', 15, 2);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('subtotal', 15, 2);
            $table->decimal('fee_pct', 5, 2);
            $table->decimal('fee_amount', 15, 2);
            $table->decimal('total', 15, 2);
            $table->decimal('creator_payout', 15, 2)->comment('amount creator receives = subtotal - fee');

            // Voucher
            $table->string('voucher_code', 50)->nullable();
            $table->decimal('voucher_discount', 15, 2)->default(0);

            // Payment
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'expired', 'refunded'])->default('pending');
            $table->string('payment_method', 50)->nullable()->comment('duitku_va_bca, duitku_gopay, etc.');
            $table->string('duitku_reference')->nullable()->unique();
            $table->string('duitku_invoice_id')->nullable()->unique();
            $table->json('duitku_response')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();

            $table->index(['creator_user_id', 'payment_status']);
            $table->index(['buyer_email', 'payment_status']);
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
