<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('status')->index();
        });

        // Mark Alice's best-selling product as featured
        \DB::statement("
            UPDATE products SET is_featured = 1
            WHERE id = (
                SELECT id FROM (
                    SELECT id FROM products
                    WHERE user_id = (SELECT id FROM users WHERE username = 'demo_alice')
                    AND status = 'published'
                    ORDER BY sales_count DESC, view_count DESC
                    LIMIT 1
                ) AS t
            )
        ");
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_featured']);
            $table->dropColumn('is_featured');
        });
    }
};
