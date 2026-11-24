<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('cost_price', 12, 2)->default(0)->after('price');
            $table->unsignedInteger('production_minutes')->default(0)->after('cost_price');
            $table->string('quote_category', 30)->nullable()->after('production_minutes');
            $table->boolean('employee_eligible')->default(false)->after('quote_category');
            $table->decimal('model_list_price', 12, 2)->nullable()->after('employee_eligible');
            $table->decimal('intl_usd_price', 12, 2)->nullable()->after('model_list_price');
            $table->boolean('is_quote_enabled')->default(false)->after('intl_usd_price');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'cost_price',
                'production_minutes',
                'quote_category',
                'employee_eligible',
                'model_list_price',
                'intl_usd_price',
                'is_quote_enabled',
            ]);
        });
    }
};
