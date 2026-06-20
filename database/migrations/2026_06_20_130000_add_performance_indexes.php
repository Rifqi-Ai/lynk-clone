<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Orders: creator dashboard queries filter by creator_user_id + status + date
        Schema::table('orders', function (Blueprint $table) {
            // Dashboard revenue queries: WHERE creator_user_id = ? AND payment_status = 'paid' GROUP BY date
            $table->index(['creator_user_id', 'paid_at'], 'orders_creator_paid_at_idx');
            // Customer lookup by email for receipts + course access
            $table->index(['buyer_email', 'created_at'], 'orders_buyer_email_created_at_idx');
            // Cleanup job: WHERE payment_status = 'pending' AND expired_at < ?
            $table->index(['payment_status', 'expired_at'], 'orders_status_expired_idx');
            // Analytics: WHERE created_at >= ? AND payment_status = 'paid'
            $table->index(['paid_at', 'payment_status'], 'orders_paid_at_status_idx');
        });

        // Products: discover/feed pages
        Schema::table('products', function (Blueprint $table) {
            // Filter by status + type: WHERE status='published' AND type='course' ORDER BY sales_count DESC
            $table->index(['status', 'type', 'sales_count'], 'products_status_type_sales_idx');
            // Featured products on landing
            $table->index(['status', 'is_featured', 'created_at'], 'products_status_featured_idx');
        });

        // Users: email is unique already via column constraint, but for case-insensitive lookup
        // (some dbs don't have native case-insensitive indexes; we add generated lower(email) index)
        // Skip for sqlite (no function index); but add it conditionally for mysql/pgsql in production
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('users', function (Blueprint $table) {
                $table->index('username');
                $table->index('phone');
            });
        }

        // Cart items: lookups by cart_id + product_id (de-dup on add)
        Schema::table('cart_items', function (Blueprint $table) {
            $table->index(['cart_id', 'product_id'], 'cart_items_cart_product_idx');
        });

        // Course progress: WHERE user_id = ? AND product_id = ?
        Schema::table('course_progress', function (Blueprint $table) {
            $table->index(['user_id', 'product_id'], 'course_progress_user_product_idx');
        });

        // Course modules: WHERE product_id = ? ORDER BY order_index
        Schema::table('course_modules', function (Blueprint $table) {
            $table->index(['product_id', 'order_index'], 'course_modules_product_order_idx');
        });

        // Event tickets: lookup by order
        Schema::table('event_tickets', function (Blueprint $table) {
            $table->index(['order_id'], 'event_tickets_order_idx');
            $table->index(['product_id', 'is_checked_in'], 'event_tickets_product_checkin_idx');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_creator_paid_at_idx');
            $table->dropIndex('orders_buyer_email_created_at_idx');
            $table->dropIndex('orders_status_expired_idx');
            $table->dropIndex('orders_paid_at_status_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_status_type_sales_idx');
            $table->dropIndex('products_status_featured_idx');
        });

        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex(['username']);
                $table->dropIndex(['phone']);
            });
        }

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropIndex('cart_items_cart_product_idx');
        });

        Schema::table('course_progress', function (Blueprint $table) {
            $table->dropIndex('course_progress_user_product_idx');
        });

        Schema::table('course_modules', function (Blueprint $table) {
            $table->dropIndex('course_modules_product_order_idx');
        });

        Schema::table('event_tickets', function (Blueprint $table) {
            $table->dropIndex('event_tickets_order_idx');
            $table->dropIndex('event_tickets_product_checkin_idx');
        });
    }
};
