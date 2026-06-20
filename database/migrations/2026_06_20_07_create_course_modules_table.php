<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_modules', function (Blueprint $table) {
            $table->id();
            $table->string('product_id', 12);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('video_url')->nullable(); // YouTube/Vimeo/direct
            $table->integer('duration_minutes')->default(0);
            $table->integer('position')->default(0);
            $table->boolean('is_free_preview')->default(false);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->index(['product_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_modules');
    }
};