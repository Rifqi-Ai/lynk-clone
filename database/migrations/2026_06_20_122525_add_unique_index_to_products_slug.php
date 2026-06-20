<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SECURITY: composite unique index on (user_id, slug) prevents race condition where
     * two concurrent requests could both insert the same slug for the same user.
     * The product controller's slug uniqueness loop is best-effort; this is the safety net.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unique(['user_id', 'slug'], 'products_user_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_user_slug_unique');
        });
    }
};
