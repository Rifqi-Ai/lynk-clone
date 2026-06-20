<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('code', 50);
            $table->enum('type', ['percent', 'fixed'])->comment('percent = % off, fixed = Rp off');
            $table->decimal('value', 15, 2)->comment('% or nominal amount');
            $table->decimal('min_purchase', 15, 2)->default(0);
            $table->decimal('max_discount', 15, 2)->nullable()->comment('cap for percent discounts');
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Code unique per creator
            $table->unique(['creator_user_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};