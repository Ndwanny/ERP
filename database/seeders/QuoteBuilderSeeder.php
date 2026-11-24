<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class QuoteBuilderSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'quote_vat_percent' => '16', 'quote_usd_rate' => '21', 'quote_partner_margin' => '25', 'quote_employee_discount' => '20', 'quote_wholesale_discount' => '15',
            'quote_pay_interest_annual' => '45', 'quote_rush_premium' => '15', 'quote_ot_weekday' => '1.5', 'quote_ot_sunday' => '2.0', 'quote_brand_margin' => '25',
            'quote_brand_screen_cost' => '50', 'quote_brand_screen_setup' => '250', 'quote_brand_embroid_cost' => '75', 'quote_brand_embroid_setup' => '400',
            'quote_brand_full_cost' => '60', 'quote_brand_full_setup' => '300', 'quote_brand_leather_patch_cost' => '80', 'quote_brand_leather_patch_setup' => '300',
            'quote_vol_tier_1' => '0', 'quote_vol_tier_2' => '2.5', 'quote_vol_tier_3' => '5', 'quote_vol_tier_4' => '10',
            'quote_mins_per_day' => '2520', 'quote_brand_days' => '2', 'quote_monthly_capacity_mins' => '79200', 'quote_monthly_fixed' => '111586',
            'quote_cb_labour' => '0.4116', 'quote_cb_sal_oh' => '0.4', 'quote_cb_fac_oh' => '0.5973', 'quote_default_valid_days' => '30',
            'quote_default_terms' => 'Payment due as agreed. Prices exclude delivery unless stated. VAT charged at prevailing ZRA rate.',
        ];
        foreach ($settings as $k => $v) {
            Setting::updateOrCreate(['key' => $k], ['value' => $v, 'type' => is_numeric($v) ? 'string' : 'string', 'group' => 'quote_builder']);
        }

        $category = ProductCategory::firstOrCreate(['slug' => 'quote-builder-bags'], ['name' => 'Quote Builder Bags', 'vertical' => 'bags']);
        $products = [
            ['name' => 'Duffle Bag (17x12x9)', 'sku' => 'QB-DUFFLE', 'cost_price' => 490, 'price' => 850, 'production_minutes' => 115, 'quote_category' => 'Complex', 'employee_eligible' => true, 'model_list_price' => 980, 'intl_usd_price' => 125],
            ['name' => 'Leather Laptop Bag 15"', 'sku' => 'QB-LEATHER', 'cost_price' => 472, 'price' => 850, 'production_minutes' => 122, 'quote_category' => 'Complex', 'employee_eligible' => true, 'model_list_price' => 944, 'intl_usd_price' => 149],
            ['name' => 'Kafue Laptop Bag 15"', 'sku' => 'QB-KAFUE', 'cost_price' => 276, 'price' => 750, 'production_minutes' => 107, 'quote_category' => 'Medium', 'employee_eligible' => true, 'model_list_price' => 613, 'intl_usd_price' => 89],
            ['name' => 'Chitenge Laptop Bag 15"', 'sku' => 'QB-CHITENGE', 'cost_price' => 326, 'price' => 650, 'production_minutes' => 112, 'quote_category' => 'Medium', 'employee_eligible' => true, 'model_list_price' => 724, 'intl_usd_price' => 79],
            ['name' => 'Backpack (Muchinga)', 'sku' => 'QB-BACKPACK', 'cost_price' => 571, 'price' => 750, 'production_minutes' => 285, 'quote_category' => 'Complex', 'employee_eligible' => false, 'model_list_price' => 1142, 'intl_usd_price' => 119],
            ['name' => 'Nyika Backpack', 'sku' => 'QB-NYIKA', 'cost_price' => 557, 'price' => 850, 'production_minutes' => 285, 'quote_category' => 'Complex', 'employee_eligible' => false, 'model_list_price' => 1114, 'intl_usd_price' => 135],
            ['name' => 'Laptop Sleeve 15"', 'sku' => 'QB-SLEEVE', 'cost_price' => 156, 'price' => 375, 'production_minutes' => 70, 'quote_category' => 'Medium', 'employee_eligible' => true, 'model_list_price' => 347, 'intl_usd_price' => 55],
            ['name' => 'Toilet Bag (22x18 cm)', 'sku' => 'QB-TOILET', 'cost_price' => 132, 'price' => 250, 'production_minutes' => 48, 'quote_category' => 'Simple', 'employee_eligible' => true, 'model_list_price' => 330, 'intl_usd_price' => 38],
            ['name' => 'Tote Bag', 'sku' => 'QB-TOTE', 'cost_price' => 119, 'price' => 275, 'production_minutes' => 45, 'quote_category' => 'Simple', 'employee_eligible' => true, 'model_list_price' => 298, 'intl_usd_price' => 42],
            ['name' => 'Chitenge Box Tote Bag', 'sku' => 'QB-BOXTOTE', 'cost_price' => 182, 'price' => 370, 'production_minutes' => 60, 'quote_category' => 'Medium', 'employee_eligible' => true, 'model_list_price' => 404, 'intl_usd_price' => 52],
            ['name' => 'Hessian Tote Bag', 'sku' => 'QB-HESSIAN', 'cost_price' => 240, 'price' => 480, 'production_minutes' => 87, 'quote_category' => 'Medium', 'employee_eligible' => true, 'model_list_price' => 533, 'intl_usd_price' => 62],
            ['name' => 'Pencil Case (32x9 cm)', 'sku' => 'QB-PENCIL', 'cost_price' => 74, 'price' => 185, 'production_minutes' => 35, 'quote_category' => 'Simple', 'employee_eligible' => false, 'model_list_price' => 185, 'intl_usd_price' => 28],
            ['name' => 'Chitenge Lanyards', 'sku' => 'QB-LANYARD', 'cost_price' => 50, 'price' => 45, 'production_minutes' => 25, 'quote_category' => 'Simple', 'employee_eligible' => false, 'model_list_price' => 125, 'intl_usd_price' => 12],
            ['name' => 'Chitenge Coin Purse', 'sku' => 'QB-COIN', 'cost_price' => 63, 'price' => 100, 'production_minutes' => 30, 'quote_category' => 'Simple', 'employee_eligible' => false, 'model_list_price' => 158, 'intl_usd_price' => 16],
            ['name' => 'Muchinga Mini (Junior)', 'sku' => 'QB-MINI', 'cost_price' => 345, 'price' => 480, 'production_minutes' => 175, 'quote_category' => 'Complex', 'employee_eligible' => false, 'model_list_price' => 690, 'intl_usd_price' => 72],
        ];

        foreach ($products as $p) {
            Product::updateOrCreate(
                ['sku' => $p['sku']],
                array_merge($p, [
                    'category_id' => $category->id,
                    'slug' => Str::slug($p['name']),
                    'description' => $p['name'].' - quote builder catalog item.',
                    'stock_quantity' => 0,
                    'is_custom_order' => false,
                    'is_published' => true,
                    'featured' => false,
                    'vertical' => 'bags',
                    'is_quote_enabled' => true,
                ])
            );
        }
    }
}
