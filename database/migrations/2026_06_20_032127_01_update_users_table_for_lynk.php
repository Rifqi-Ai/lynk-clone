<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // username = URL slug like lynk.id/<username>
            $table->string('username', 50)->unique()->nullable()->after('name');
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('avatar_path')->nullable();
            $table->string('cover_path')->nullable();
            $table->string('title', 100)->nullable()->comment('Profile subtitle like "Storyteller | Lightroom Presets"');
            $table->text('bio')->nullable();
            $table->json('social_links')->nullable()->comment('Array of {platform, url}');
            $table->json('appearance')->nullable()->comment('Theme settings');
            $table->enum('plan_tier', ['starter', 'pro', 'brandpreneur'])->default('starter');
            $table->decimal('transaction_fee_pct', 5, 2)->default(10.00);
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('total_earnings', 15, 2)->default(0);
            $table->string('google_id')->nullable()->unique();
            $table->string('custom_domain')->nullable()->unique();
            $table->boolean('verified')->default(false);
            $table->boolean('show_branding')->default(true)->comment('Show Lynk branding on profile');
            $table->index('username');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username', 'phone', 'avatar_path', 'cover_path', 'title',
                'bio', 'social_links', 'appearance', 'plan_tier',
                'transaction_fee_pct', 'balance', 'total_earnings',
                'google_id', 'custom_domain', 'verified', 'show_branding'
            ]);
        });
    }
};