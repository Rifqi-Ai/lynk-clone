<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Promote `shipping_status` from JSON-in-metadata to a first-class column on `orders`.
 *
 * **Why:** Phase 12 security audit flagged `FulfillmentController::index()`'s
 * `orderByRaw("JSON_EXTRACT(metadata, '$.shipping_status') ASC")` as a brittle
 * static SQL fragment — it would silently break if the metadata column structure
 * ever changed. Promoting to a column also fixes a latent business-logic bug:
 * the previous alphabetical ASC sort returned orders in REVERSE workflow order
 * (delivered → packed → pending → shipped), hiding pending work from creators.
 *
 * **Migration safety:**
 *  - Column is nullable to support existing non-physical orders (digital/course/event/donation).
 *  - Backfill copies current `metadata->shipping_status` value to the column for
 *    existing physical orders. Other order types are left NULL — they never use
 *    shipping_status anyway.
 *  - Default for new rows: 'pending' (most common initial state for physical orders).
 *  - Index on `(creator_user_id, payment_status, shipping_status)` keeps the
 *    fulfillment dashboard query (filter by creator + paid + status) fast.
 *
 * @see docs/security-audit-2026-06-21.md (recommendation #2)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_status', 20)
                ->nullable()
                ->after('payment_status')
                ->comment('pending|packed|shipped|delivered — first-class column, was metadata->shipping_status');
        });

        // Backfill from metadata for existing rows. SQLite + MySQL + PostgreSQL all
        // support JSON extraction via Laravel's portable whereJsonContains path;
        // here we use raw DB::raw() once for the backfill only.
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("
                UPDATE orders
                SET shipping_status = JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.shipping_status'))
                WHERE product_id IN (SELECT id FROM products WHERE type = 'physical')
                  AND metadata IS NOT NULL
                  AND JSON_EXTRACT(metadata, '$.shipping_status') IS NOT NULL
            ");
        } elseif ($driver === 'sqlite') {
            DB::statement("
                UPDATE orders
                SET shipping_status = json_extract(metadata, '$.shipping_status')
                WHERE product_id IN (SELECT id FROM products WHERE type = 'physical')
                  AND metadata IS NOT NULL
                  AND json_extract(metadata, '$.shipping_status') IS NOT NULL
            ");
        } elseif ($driver === 'pgsql') {
            DB::statement("
                UPDATE orders
                SET shipping_status = metadata->>'shipping_status'
                WHERE product_id IN (SELECT id FROM products WHERE type = 'physical')
                  AND metadata IS NOT NULL
                  AND metadata->>'shipping_status' IS NOT NULL
            ");
        }

        // Index for fast fulfillment dashboard filtering (creator + paid + status).
        Schema::table('orders', function (Blueprint $table) {
            $table->index(
                ['creator_user_id', 'payment_status', 'shipping_status'],
                'orders_fulfillment_dashboard_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_fulfillment_dashboard_idx');
            $table->dropColumn('shipping_status');
        });
    }
};
