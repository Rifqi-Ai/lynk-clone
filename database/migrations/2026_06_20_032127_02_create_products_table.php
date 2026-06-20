<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            // 12-char nanoid style id like lynk.id's xxevrvd7mm0g
            $table->string('id', 12)->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // type: digital (MVP), blog, appointment, course, event, donation, physical
            $table->enum('type', ['digital', 'blog', 'appointment', 'course', 'event', 'donation', 'physical'])
                  ->default('digital');

            $table->string('title', 200);
            $table->string('slug', 200);
            $table->text('description')->nullable();

            // Pricing (all in IDR for MVP)
            $table->decimal('price', 15, 2);
            $table->decimal('compare_at_price', 15, 2)->nullable()->comment('Original/strikethrough price');

            // Media
            $table->string('thumbnail_path')->nullable();

            // Digital product specific
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedInteger('download_limit_per_purchase')->default(5);

            // Polymorphic bag for other types (json)
            $table->json('metadata')->nullable()->comment('Type-specific fields (course duration, event date, etc)');

            // Status & analytics
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->unsignedInteger('sales_count')->default(0);
            $table->unsignedInteger('view_count')->default(0);

            $table->timestamps();

            // Composite unique for owner + slug
            $table->unique(['user_id', 'slug']);
            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};