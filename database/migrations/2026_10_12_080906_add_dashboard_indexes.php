<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->index(['status', 'currency', 'paid_at'], 'payments_dashboard_idx');
            $table->index(['status', 'created_at'], 'payments_status_created_idx');
        });
        Schema::table('properties', function (Blueprint $table) {
            $table->index('created_at', 'properties_created_idx');
            $table->index(['status', 'reviewed_at'], 'properties_reviewed_idx');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->index('created_at', 'users_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_dashboard_idx');
            $table->dropIndex('payments_status_created_idx');
        });
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex('properties_created_idx');
            $table->dropIndex('properties_reviewed_idx');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_created_idx');
        });
    }
};
