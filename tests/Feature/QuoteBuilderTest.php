<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteBuilderTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'super-admin'): User
    {
        $this->seed(RoleSeeder::class);

        return User::create([
            'role_id' => Role::where('name', $role)->value('id'),
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => $role.'@example.test',
            'email_verified_at' => now(),
            'password' => bcrypt('ChangeMe@2026!'),
        ]);
    }

    private function quoteProduct(): Product
    {
        $cat = ProductCategory::create(['name' => 'Bags', 'slug' => 'bags', 'vertical' => 'bags']);

        return Product::create([
            'category_id' => $cat->id, 'name' => 'Duffle Bag', 'slug' => 'duffle-bag', 'sku' => 'QB-TEST-1',
            'price' => 850, 'cost_price' => 490, 'production_minutes' => 115, 'quote_category' => 'Complex',
            'employee_eligible' => true, 'is_quote_enabled' => true, 'is_published' => true, 'vertical' => 'bags',
            'stock_quantity' => 0,
        ]);
    }

    private function client(): Client
    {
        return Client::create(['name' => 'Acme Corp', 'email' => 'acme@example.test', 'type' => 'corporate', 'status' => 'active']);
    }

    public function test_quote_builder_page_requires_invoicing_access(): void
    {
        $user = $this->user('production');
        $this->actingAs($user)->get('/invoicing/quote-builder')->assertForbidden();
    }

    public function test_quote_builder_page_renders_for_invoicing_access(): void
    {
        $this->actingAs($this->user('super-admin'))
            ->get(route('invoicing.quote-builder.create'))
            ->assertOk()
            ->assertSee('quote-builder', false);
    }

    public function test_quote_builder_saves_server_calculated_quote(): void
    {
        $user = $this->user('super-admin');
        $product = $this->quoteProduct();
        $client = $this->client();

        $response = $this->actingAs($user)->post(route('invoicing.quote-builder.store'), [
            'client_id' => $client->id,
            'issue_date' => '2026-06-10',
            'valid_days' => 30,
            'currency' => 'ZMW',
            'discount_percent' => 0,
            'tax_percent' => 16,
            'items' => [[
                'product_id' => $product->id,
                'quantity' => 2,
                'channel' => 'retail',
                'branding' => 'none',
                'payment_days' => 0,
                'rush' => 0,
                'manual_discount_percent' => 0,
                'strategic_discount_percent' => 0,
            ]],
        ]);

        $invoice = Invoice::firstOrFail();
        $response->assertRedirect(route('invoicing.show', $invoice));
        $this->assertSame('quote', $invoice->type);
        $this->assertEquals(1700.00, (float) $invoice->subtotal);
        $this->assertEquals(272.00, (float) $invoice->tax_amount);
        $this->assertEquals(1972.00, (float) $invoice->total);
        $this->assertCount(1, $invoice->items);
        $this->assertSame($product->id, data_get($invoice->items->first()->meta, 'product_id'));
        $this->assertSame('advanced_quote_builder_v1', data_get($invoice->meta, 'builder'));
    }

    public function test_quote_builder_recalculates_tampered_totals(): void
    {
        $user = $this->user('super-admin');
        $product = $this->quoteProduct();
        $client = $this->client();

        $this->actingAs($user)->post(route('invoicing.quote-builder.store'), [
            'client_id' => $client->id,
            'issue_date' => '2026-06-10',
            'discount_percent' => 0,
            'tax_percent' => 16,
            'subtotal' => 1,
            'total' => 1,
            'items' => [[
                'product_id' => $product->id,
                'quantity' => 1,
                'channel' => 'retail',
                'branding' => 'none',
                'payment_days' => 0,
                'rush' => 0,
                'manual_discount_percent' => 0,
                'strategic_discount_percent' => 0,
            ]],
        ])->assertRedirect();

        $invoice = Invoice::firstOrFail();
        $this->assertEquals(850.00, (float) $invoice->subtotal);
        $this->assertEquals(986.00, (float) $invoice->total);
    }
}
