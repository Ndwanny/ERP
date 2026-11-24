<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->json('meta')->nullable()->after('terms');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->json('meta')->nullable()->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn('meta');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('meta');
        });
    }
};
