<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->string('id', 20)->primary()->comment('e.g. CART-XXXXXX');
            // Cart belongs to a creator (you can only buy from one creator at a time)
            $table->foreignId('creator_user_id')->constrained('users')->cascadeOnDelete();
            // Buyer — can be guest (null) or registered user
            $table->foreignId('buyer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('buyer_email', 191)->nullable();
            // Voucher applied
            $table->foreignId('voucher_id')->nullable()->constrained('vouchers')->nullOnDelete();
            $table->decimal('voucher_discount', 15, 2)->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['creator_user_id', 'buyer_email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
