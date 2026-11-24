<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Setting;
use InvalidArgumentException;

class QuoteBuilderService
{
    public function settings(): array
    {
        $defaults = $this->defaults();
        $stored = Setting::whereIn('key', array_keys($defaults))->pluck('value', 'key');
        $settings = [];
        foreach ($defaults as $key => $default) {
            $raw = $stored[$key] ?? null;
            $settings[$key] = $raw === null || $raw === '' ? $default : $this->cast($key, $raw, $default);
        }

        return $settings;
    }

    public function defaults(): array
    {
        return [
            'quote_vat_percent' => 16.0, 'quote_usd_rate' => 21.0,
            'quote_partner_margin' => 25.0, 'quote_employee_discount' => 20.0, 'quote_wholesale_discount' => 15.0,
            'quote_pay_interest_annual' => 45.0, 'quote_rush_premium' => 15.0,
            'quote_ot_weekday' => 1.5, 'quote_ot_sunday' => 2.0, 'quote_brand_margin' => 25.0,
            'quote_brand_screen_cost' => 50.0, 'quote_brand_screen_setup' => 250.0,
            'quote_brand_embroid_cost' => 75.0, 'quote_brand_embroid_setup' => 400.0,
            'quote_brand_full_cost' => 60.0, 'quote_brand_full_setup' => 300.0,
            'quote_brand_leather_patch_cost' => 80.0, 'quote_brand_leather_patch_setup' => 300.0,
            'quote_vol_tier_1' => 0.0, 'quote_vol_tier_2' => 2.5, 'quote_vol_tier_3' => 5.0, 'quote_vol_tier_4' => 10.0,
            'quote_mins_per_day' => 2520, 'quote_brand_days' => 2, 'quote_monthly_capacity_mins' => 79200, 'quote_monthly_fixed' => 111586.0,
            'quote_cb_labour' => 0.4116, 'quote_cb_sal_oh' => 0.4000, 'quote_cb_fac_oh' => 0.5973,
            'quote_default_valid_days' => 30,
            'quote_default_terms' => 'Payment due as agreed. Prices exclude delivery unless stated. VAT charged at prevailing ZRA rate.',
        ];
    }

    private function cast(string $key, string $raw, mixed $default): mixed
    {
        if (is_int($default)) {
            return (int) $raw;
        }
        if (is_float($default)) {
            return (float) $raw;
        }

        return $raw;
    }

    public function quoteProducts()
    {
        return Product::query()->where('is_quote_enabled', true)->orderBy('name')->get([
            'id', 'name', 'sku', 'vertical', 'price', 'cost_price', 'production_minutes', 'quote_category', 'employee_eligible', 'model_list_price', 'intl_usd_price',
        ]);
    }

    public function volumeDiscount(int $qty, array $s): float
    {
        if ($qty >= 500) {
            return (float) $s['quote_vol_tier_4'] / 100;
        }
        if ($qty >= 200) {
            return (float) $s['quote_vol_tier_3'] / 100;
        }
        if ($qty >= 50) {
            return (float) $s['quote_vol_tier_2'] / 100;
        }

        return (float) $s['quote_vol_tier_1'] / 100;
    }

    public function branding(string $brand, int $qty, array $s): array
    {
        $map = [
            'none' => [0.0, 0.0],
            'screen' => [(float) $s['quote_brand_screen_cost'], (float) $s['quote_brand_screen_setup']],
            'embroid' => [(float) $s['quote_brand_embroid_cost'], (float) $s['quote_brand_embroid_setup']],
            'full' => [(float) $s['quote_brand_full_cost'], (float) $s['quote_brand_full_setup']],
            'leather_patch' => [(float) $s['quote_brand_leather_patch_cost'], (float) $s['quote_brand_leather_patch_setup']],
        ];
        if (! isset($map[$brand])) {
            throw new InvalidArgumentException('Invalid branding option.');
        }
        [$contractor, $setup] = $map[$brand];
        $brandMargin = max(0.0, min(0.95, (float) $s['quote_brand_margin'] / 100));
        $qty = max(1, $qty);
        $brandCost = $brand === 'none' ? 0.0 : (($contractor + $setup / $qty) / (1 - $brandMargin));

        return ['contractor' => $contractor, 'setup' => $setup, 'setup_per_unit' => $brand === 'none' ? 0.0 : $setup / $qty, 'brand_cost' => $brandCost];
    }

    public function calculateLine(Product $product, array $line, array $s): array
    {
        $qty = max(1, (int) ($line['quantity'] ?? 1));
        $channel = (string) ($line['channel'] ?? 'retail');
        $brand = (string) ($line['branding'] ?? 'none');
        $payDays = max(0, (int) ($line['payment_days'] ?? 0));
        $rush = (bool) ($line['rush'] ?? false);
        $manualDisc = max(0, min(80, (float) ($line['manual_discount_percent'] ?? 0)));
        $stratDisc = max(0, min(80, (float) ($line['strategic_discount_percent'] ?? 0)));
        $discPct = min(80, $manualDisc + $stratDisc);

        $cost = (float) $product->cost_price;
        $retail = (float) $product->price;
        $partnerM = (float) $s['quote_partner_margin'] / 100;
        $empDisc = (float) $s['quote_employee_discount'] / 100;
        $wholeDisc = (float) $s['quote_wholesale_discount'] / 100;
        $payRate = (float) $s['quote_pay_interest_annual'] / 100;
        $rushP = (float) $s['quote_rush_premium'] / 100;
        $branding = $this->branding($brand, $qty, $s);
        $volDisc = $payDays <= 15 ? $this->volumeDiscount($qty, $s) : 0.0;
        $bagBase = $retail * (1 - $volDisc);
        $payUplift = ($payDays / 365) * $payRate;
        $instPrice = ($bagBase + $branding['brand_cost']) * (1 + $payUplift);
        if ($rush) {
            $instPrice *= (1 + $rushP);
        }
        $instCost = $cost + $branding['contractor'] + $branding['setup_per_unit'];

        $channels = [
            'retail' => ['price' => $retail, 'cost' => $cost, 'label' => 'MPS Retail'],
            'partner' => ['price' => $retail * (1 - $partnerM), 'cost' => $cost, 'label' => 'Retail Partner'],
            'institutional' => ['price' => $instPrice, 'cost' => $instCost, 'label' => 'Institutional'],
            'employee' => ['price' => $product->employee_eligible ? $retail * (1 - $empDisc) : null, 'cost' => $cost, 'label' => 'Employee'],
            'wholesale' => ['price' => $retail * (1 - $wholeDisc), 'cost' => $cost, 'label' => 'Wholesale'],
        ];
        if (! isset($channels[$channel])) {
            throw new InvalidArgumentException('Invalid channel.');
        }
        $basePrice = $channels[$channel]['price'];
        if ($basePrice === null) {
            throw new InvalidArgumentException('Product is not employee-eligible.');
        }
        $unitPrice = round($basePrice * (1 - $discPct / 100), 2);
        $lineSubtotal = round($unitPrice * $qty, 2);
        $lineCost = (float) $channels[$channel]['cost'];
        $marginAmount = round($lineSubtotal - ($lineCost * $qty), 2);
        $marginPercent = $lineSubtotal > 0 ? round(($marginAmount / $lineSubtotal) * 100, 2) : 0.0;

        return [
            'product_id' => $product->id, 'name' => $product->name, 'sku' => $product->sku, 'quantity' => $qty, 'unit' => 'pcs',
            'channel' => $channel, 'channel_label' => $channels[$channel]['label'], 'branding' => $brand, 'payment_days' => $payDays, 'rush' => $rush,
            'manual_discount_percent' => $manualDisc, 'strategic_discount_percent' => $stratDisc, 'discount_percent' => $discPct,
            'base_unit_price' => round($basePrice, 2), 'unit_price' => $unitPrice, 'line_total' => $lineSubtotal,
            'cost_unit' => round($lineCost, 2), 'cost_total' => round($lineCost * $qty, 2), 'margin_amount' => $marginAmount, 'margin_percent' => $marginPercent,
            'production_minutes' => (int) $product->production_minutes, 'branding_meta' => $branding, 'volume_discount_percent' => round($volDisc * 100, 2),
        ];
    }

    public function buildQuote(array $payload, array $s): array
    {
        $lines = [];
        foreach (($payload['items'] ?? []) as $item) {
            $product = Product::findOrFail($item['product_id']);
            if (! $product->is_quote_enabled) {
                throw new InvalidArgumentException('Product is not quote-enabled.');
            }
            $calc = $this->calculateLine($product, $item, $s);
            $desc = $calc['name'];
            $notes = [];
            if ($calc['branding'] !== 'none') {
                $notes[] = 'Branding: '.str_replace('_', ' ', $calc['branding']);
            }
            if ($calc['payment_days'] > 0) {
                $notes[] = $calc['payment_days'].'-day terms';
            }
            if ($calc['rush']) {
                $notes[] = 'Rush order';
            }
            if ($calc['discount_percent'] > 0) {
                $notes[] = 'Discount: '.rtrim(rtrim(number_format($calc['discount_percent'], 2), '0'), '.').'%';
            }
            if ($calc['channel'] !== 'retail') {
                $notes[] = 'Channel: '.$calc['channel_label'];
            }
            if ($notes) {
                $desc .= ' ('.implode('; ', $notes).')';
            }
            $lines[] = ['description' => $desc, 'quantity' => $calc['quantity'], 'unit' => 'pcs', 'unit_price' => $calc['unit_price'], 'total' => $calc['line_total'], 'meta' => $calc];
        }
        if (! $lines) {
            throw new InvalidArgumentException('At least one line item is required.');
        }
        $subtotal = round(array_sum(array_column($lines, 'total')), 2);
        $docDiscountPercent = max(0, min(100, (float) ($payload['discount_percent'] ?? 0)));
        $docDiscountAmount = round($subtotal * $docDiscountPercent / 100, 2);
        $taxPercent = max(0, min(100, (float) ($payload['tax_percent'] ?? $s['quote_vat_percent'])));
        $taxable = round($subtotal - $docDiscountAmount, 2);
        $taxAmount = round($taxable * $taxPercent / 100, 2);
        $total = round($taxable + $taxAmount, 2);
        $totalMinutes = array_sum(array_map(fn ($l) => $l['meta']['production_minutes'] * $l['quantity'], $lines));
        $prodDays = (int) ceil($totalMinutes / max(1, (int) $s['quote_mins_per_day']));
        $brandDays = collect($lines)->contains(fn ($l) => $l['meta']['branding'] !== 'none') ? (int) $s['quote_brand_days'] : 0;

        return [
            'lines' => $lines, 'subtotal' => $subtotal, 'discount_percent' => $docDiscountPercent, 'discount_amount' => $docDiscountAmount,
            'tax_percent' => $taxPercent, 'tax_amount' => $taxAmount, 'total' => $total,
            'meta' => [
                'builder' => 'advanced_quote_builder_v1',
                'lead_time' => $payload['lead_time'] ?? null,
                'prepared_by' => $payload['prepared_by'] ?? null,
                'internal' => [
                    'cost_total' => round(array_sum(array_map(fn ($l) => $l['meta']['cost_total'], $lines)), 2),
                    'margin_amount' => round(array_sum(array_map(fn ($l) => $l['meta']['margin_amount'], $lines)), 2),
                    'production_minutes' => $totalMinutes,
                    'lead_days' => $prodDays + $brandDays,
                ],
            ],
        ];
    }
}
