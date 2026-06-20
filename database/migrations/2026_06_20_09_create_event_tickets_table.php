<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('order_id', 20);
            $table->string('product_id', 12);
            $table->string('buyer_email');
            $table->string('attendee_name')->nullable();
            $table->string('ticket_code', 20)->unique(); // short code for check-in
            $table->boolean('is_checked_in')->default(false);
            $table->timestamp('checked_in_at')->nullable();
            $table->string('checked_in_by')->nullable(); // creator email/name
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->index(['product_id', 'is_checked_in']);
            $table->index('buyer_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_tickets');
    }
};
