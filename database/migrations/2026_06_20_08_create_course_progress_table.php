<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_progress', function (Blueprint $table) {
            $table->id();
            $table->string('order_id', 20);
            $table->unsignedBigInteger('module_id');
            $table->string('buyer_email');
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('module_id')->references('id')->on('course_modules')->cascadeOnDelete();
            $table->unique(['order_id', 'module_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_progress');
    }
};