<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\ChartOfAccount;
use App\Models\Client;
use App\Models\ClientInteraction;
use App\Models\Department;
use App\Models\EcommerceOrder;
use App\Models\EcommerceOrderItem;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Lead;
use App\Models\LeaveRequest;
use App\Models\MarketingCampaign;
use App\Models\Payment;
use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductionOrder;
use App\Models\ProductionStageLog;
use App\Models\Project;
use App\Models\ProjectContractor;
use App\Models\ProjectMilestone;
use App\Models\ProjectSiteVisit;
use App\Models\RetailCustomer;
use App\Models\SocialPost;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (! config('app.seed_demo_data')) {
            return;
        }

        Model::unguarded(function () {
            $this->seedDemo();
        });

        $this->command?->info('MPS demo data seeded.');
    }

    private function seedDemo(): void
    {
        $admin = $this->user('admin@myperfectstitch.co.zm') ?? User::first();
        $sales = $this->user('sales@myperfectstitch.co.zm') ?? $admin;
        $finance = $this->user('finance@myperfectstitch.co.zm') ?? $admin;
        $production = $this->user('production@myperfectstitch.co.zm') ?? $admin;
        $design = $this->user('design@myperfectstitch.co.zm') ?? $admin;
        $hr = $this->user('hr@myperfectstitch.co.zm') ?? $admin;
        $marketing = $this->user('marketing@myperfectstitch.co.zm') ?? $admin;
        $projectsUser = $this->user('projects@myperfectstitch.co.zm') ?? $admin;

        $today = today()->toDateString();
        $now = now();

        $categories = [];
        foreach ([
            ['Fabrics & Chitenge', 'bags', '#B37A1A'],
            ['Leather & Hardware', 'bags', '#6D4C41'],
            ['Foam & Upholstery', 'furniture', '#2E7D32'],
            ['Timber & Board', 'furniture', '#5D4037'],
            ['Finishes & Accessories', 'interiors', '#1565C0'],
            ['Packaging', 'shared', '#616161'],
        ] as $category) {
            $key = $category[0];
            $categories[$key] = InventoryCategory::firstOrCreate(
                ['name' => $category[0]],
                ['vertical' => $category[1], 'color_code' => $category[2]]
            );
        }

        $itemDefs = [
            ['FAB-CHT-001', 'Fabrics & Chitenge', 'Kitenge / Chitenge Print Fabric', 'metres', 120, 30, 45, 'Zambia Fabrics Ltd', 'Aisle A1', 'in_stock'],
            ['FAB-CNV-002', 'Fabrics & Chitenge', 'Canvas 12oz Natural', 'metres', 85, 20, 68, 'Zambia Fabrics Ltd', 'Aisle A2', 'in_stock'],
            ['FAB-LTH-003', 'Leather & Hardware', 'Full Grain Leather Hide', 'sq ft', 12, 15, 120, 'Lusaka Leather Works', 'Cage C1', 'low_stock'],
            ['HRD-ZIP-004', 'Leather & Hardware', 'YKK Zipper 60cm', 'pcs', 240, 50, 8, 'Hardware House Zambia', 'Bin B4', 'in_stock'],
            ['HRD-BKL-005', 'Leather & Hardware', 'Metal Buckle 40mm', 'pcs', 300, 60, 5, 'Hardware House Zambia', 'Bin B5', 'in_stock'],
            ['FUR-FOM-006', 'Foam & Upholstery', 'High Density Foam 4 inch', 'sheets', 26, 10, 350, 'Foam Promotions Zambia', 'Store F2', 'in_stock'],
            ['FUR-TMB-007', 'Timber & Board', 'Mahogany Timber 2x4', 'pcs', 40, 12, 95, 'Timber Zambia Limited', 'Yard T1', 'in_stock'],
            ['INT-PNT-008', 'Finishes & Accessories', 'Interior Emulsion Paint 5L', 'tins', 8, 6, 420, 'Paint Masters Zambia', 'Store I1', 'in_stock'],
            ['PKG-BOX-009', 'Packaging', 'Premium Gift Box Large', 'pcs', 150, 40, 12, 'Packaging Solutions', 'Bin P1', 'in_stock'],
        ];

        $items = [];
        foreach ($itemDefs as $def) {
            $items[$def[0]] = InventoryItem::firstOrCreate(
                ['sku' => $def[0]],
                [
                    'category_id' => $categories[$def[1]]->id,
                    'name' => $def[2],
                    'unit' => $def[3],
                    'quantity' => $def[4],
                    'min_quantity' => $def[5],
                    'unit_cost' => $def[6],
                    'supplier' => $def[7],
                    'location' => $def[8],
                    'status' => $def[9],
                    'description' => 'Local demo stock record for '.$def[2].'.',
                ]
            );
        }

        $this->seedChildren('inventory_transactions', function () use ($items, $production, $now) {
            $transactions = [
                ['FAB-CHT-001', 'restock', 120, 0, 120, 'PO-2026-101'],
                ['FAB-CNV-002', 'restock', 100, 0, 100, 'PO-2026-102'],
                ['FAB-CNV-002', 'deduction', 15, 100, 85, 'MPS-ORD-2026-0001'],
                ['FAB-LTH-003', 'restock', 30, 0, 30, 'PO-2026-103'],
                ['FAB-LTH-003', 'deduction', 18, 30, 12, 'MPS-ORD-2026-0004'],
                ['HRD-ZIP-004', 'restock', 240, 0, 240, 'PO-2026-104'],
                ['HRD-BKL-005', 'restock', 300, 0, 300, 'PO-2026-104'],
                ['FUR-FOM-006', 'restock', 26, 0, 26, 'PO-2026-105'],
                ['FUR-TMB-007', 'restock', 40, 0, 40, 'PO-2026-106'],
                ['INT-PNT-008', 'adjustment', 8, 0, 8, 'STOCK-TAKE-2026-11'],
                ['PKG-BOX-009', 'restock', 160, 0, 160, 'PO-2026-107'],
                ['PKG-BOX-009', 'damage', 10, 160, 150, 'DAMAGE-2026-014'],
            ];
            foreach ($transactions as $t) {
                InventoryTransaction::create([
                    'item_id' => $items[$t[0]]->id,
                    'user_id' => $production->id,
                    'type' => $t[1],
                    'quantity' => $t[2],
                    'quantity_before' => $t[3],
                    'quantity_after' => $t[4],
                    'reference' => $t[5],
                    'notes' => 'Local demo stock movement.',
                    'created_at' => $now->copy()->subDays(rand(1, 20)),
                    'updated_at' => $now,
                ]);
            }
        });

        $clientDefs = [
            ['ZESCO Limited', 'institutional', 'Energy & Utilities', 'Mulenga Phiri', 'procurement@zesco.co.zm', '+260 211 123456', 'active', 'enterprise', 'Corporate outreach'],
            ['ABSA Bank Zambia', 'corporate', 'Banking & Finance', 'Chileshe Banda', 'corporate@absa.co.zm', '+260 211 378000', 'active', 'enterprise', 'Referral'],
            ['Airtel Zambia', 'corporate', 'Telecommunications', 'Mwansa Silwamba', 'mwansa.silwamba@airtel.co.zm', '+260 97 8000000', 'active', 'vip', 'Trade exhibition'],
            ['ZANACO', 'corporate', 'Banking & Finance', 'Nalishebo Mwale', 'corporate@zanaco.co.zm', '+260 211 399000', 'active', 'enterprise', 'Existing client'],
            ['Chibesa Retail', 'retail', 'Retail & Fashion', 'Chibesa Mwale', 'chibesa.mwale@gmail.com', '+260 97 1234567', 'active', 'standard', 'Instagram'],
        ];

        $lifetimeValues = [
            'ZESCO Limited' => 203000,
            'ABSA Bank Zambia' => 111360,
            'Airtel Zambia' => 71920,
            'ZANACO' => 128760,
            'Chibesa Retail' => 5626,
        ];

        $clients = [];
        foreach ($clientDefs as $def) {
            $clients[$def[0]] = Client::firstOrCreate(
                ['name' => $def[0]],
                [
                    'type' => $def[1],
                    'company' => $def[0],
                    'industry' => $def[2],
                    'contact_person' => $def[3],
                    'email' => $def[4],
                    'phone' => $def[5],
                    'city' => 'Lusaka',
                    'country' => 'Zambia',
                    'status' => $def[6],
                    'tier' => $def[7],
                    'referral_source' => $def[8],
                    'assigned_to' => $sales->id,
                    'notes' => 'Local demo client record.',
                    'lifetime_value' => $lifetimeValues[$def[0]],
                ]
            );
        }

        $this->seedChildren('client_interactions', function () use ($clients, $sales, $now) {
            $interactions = [
                ['ZESCO Limited', 'meeting', 'Uniform bag specification review', 'Discussed 500 branded laptop bags for staff induction.', today()->addDays(7)->toDateString()],
                ['ZESCO Limited', 'email', 'Quotation follow-up', 'Sent quotation QUO-2026-0001 for approval.', today()->addDays(3)->toDateString()],
                ['ABSA Bank Zambia', 'whatsapp', 'Backpack samples', 'Shared sample photos of executive backpacks.', today()->addDays(5)->toDateString()],
                ['Airtel Zambia', 'call', 'Delivery schedule', 'Confirmed urgent delivery window for travel bags.', today()->addDays(2)->toDateString()],
                ['ZANACO', 'site_visit', 'Boardroom measurement survey', 'Measured boardroom for custom fit-out.', today()->addDays(10)->toDateString()],
                ['Chibesa Retail', 'note', 'Retail order enquiry', 'Customer requested handcrafted chairs and tote bags.', null],
            ];
            foreach ($interactions as $i) {
                ClientInteraction::create([
                    'client_id' => $clients[$i[0]]->id,
                    'user_id' => $sales->id,
                    'type' => $i[1],
                    'subject' => $i[2],
                    'notes' => $i[3],
                    'follow_up_date' => $i[4],
                    'created_at' => $now->copy()->subDays(rand(1, 12)),
                    'updated_at' => $now,
                ]);
            }
        });

        $invoiceSpecs = [
            [
                'invoice_number' => 'QUO-2026-0001',
                'client' => 'ZESCO Limited',
                'type' => 'quote',
                'status' => 'sent',
                'vertical' => 'bags',
                'issue_date' => today()->subDays(10)->toDateString(),
                'due_date' => today()->addDays(20)->toDateString(),
                'tax_percent' => 16,
                'items' => [
                    ['500 Branded Laptop Bags', 500, 'pcs', 350],
                ],
                'sent_at' => $now->copy()->subDays(9),
                'paid_at' => null,
                'amount_paid' => 0,
                'payment' => null,
            ],
            [
                'invoice_number' => 'PRO-2026-0002',
                'client' => 'ABSA Bank Zambia',
                'type' => 'proforma',
                'status' => 'viewed',
                'vertical' => 'bags',
                'issue_date' => today()->subDays(8)->toDateString(),
                'due_date' => today()->addDays(10)->toDateString(),
                'tax_percent' => 16,
                'items' => [
                    ['200 Executive Backpacks', 200, 'pcs', 480],
                ],
                'sent_at' => $now->copy()->subDays(7),
                'paid_at' => null,
                'amount_paid' => 0,
                'payment' => null,
            ],
            [
                'invoice_number' => 'INV-2026-0003',
                'client' => 'ZANACO',
                'type' => 'invoice',
                'status' => 'paid',
                'vertical' => 'furniture',
                'issue_date' => today()->subDays(20)->toDateString(),
                'due_date' => today()->subDays(5)->toDateString(),
                'tax_percent' => 16,
                'items' => [
                    ['30 Ergonomic Office Chairs', 30, 'pcs', 2200],
                    ['10 Executive Desks', 10, 'pcs', 4500],
                ],
                'sent_at' => $now->copy()->subDays(19),
                'paid_at' => $now,
                'amount_paid' => 'full',
                'payment' => ['bank_transfer', 'FNB-TRF-2026-8841', $today],
            ],
            [
                'invoice_number' => 'INV-2026-0004',
                'client' => 'Airtel Zambia',
                'type' => 'invoice',
                'status' => 'overdue',
                'vertical' => 'bags',
                'issue_date' => today()->subDays(40)->toDateString(),
                'due_date' => today()->subDays(10)->toDateString(),
                'tax_percent' => 16,
                'items' => [
                    ['100 Travel Bags', 100, 'pcs', 620],
                ],
                'sent_at' => $now->copy()->subDays(39),
                'paid_at' => null,
                'amount_paid' => 0,
                'payment' => null,
            ],
            [
                'invoice_number' => 'INV-2026-0005',
                'client' => 'Chibesa Retail',
                'type' => 'invoice',
                'status' => 'partial',
                'vertical' => 'mixed',
                'issue_date' => today()->subDays(5)->toDateString(),
                'due_date' => today()->addDays(25)->toDateString(),
                'tax_percent' => 16,
                'items' => [
                    ['2 Handcrafted Lounge Chairs', 2, 'pcs', 1800],
                    ['5 Chitenge Tote Bags', 5, 'pcs', 250],
                ],
                'sent_at' => $now->copy()->subDays(4),
                'paid_at' => null,
                'amount_paid' => 2000,
                'payment' => ['mobile_money', 'AIRTEL-MM-77129', today()->subDays(3)->toDateString()],
            ],
        ];

        $invoices = [];
        foreach ($invoiceSpecs as $spec) {
            $subtotal = 0;
            foreach ($spec['items'] as $line) {
                $subtotal += $line[1] * $line[3];
            }
            $taxAmount = round($subtotal * $spec['tax_percent'] / 100, 2);
            $total = round($subtotal + $taxAmount, 2);
            $amountPaid = $spec['amount_paid'] === 'full' ? $total : (float) $spec['amount_paid'];

            $invoice = Invoice::firstOrCreate(
                ['invoice_number' => $spec['invoice_number']],
                [
                    'client_id' => $clients[$spec['client']]->id,
                    'created_by' => $admin->id,
                    'type' => $spec['type'],
                    'status' => $spec['status'],
                    'vertical' => $spec['vertical'],
                    'issue_date' => $spec['issue_date'],
                    'due_date' => $spec['due_date'],
                    'subtotal' => $subtotal,
                    'discount_percent' => 0,
                    'discount_amount' => 0,
                    'tax_percent' => $spec['tax_percent'],
                    'tax_amount' => $taxAmount,
                    'total' => $total,
                    'amount_paid' => $amountPaid,
                    'balance_due' => round($total - $amountPaid, 2),
                    'currency' => 'ZMW',
                    'notes' => 'Local demo invoice.',
                    'terms' => 'Payment due within 30 days. VAT at ZRA standard rate.',
                    'sent_at' => $spec['sent_at'],
                    'paid_at' => $spec['paid_at'],
                ]
            );
            $invoices[$spec['invoice_number']] = $invoice;

            if ($invoice->items()->count() === 0) {
                foreach ($spec['items'] as $index => $line) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'description' => $line[0],
                        'quantity' => $line[1],
                        'unit' => $line[2],
                        'unit_price' => $line[3],
                        'total' => $line[1] * $line[3],
                        'sort_order' => $index,
                    ]);
                }
            }

            if ($spec['payment']) {
                $payment = Payment::firstOrCreate(
                    ['invoice_id' => $invoice->id, 'reference' => $spec['payment'][1]],
                    [
                        'recorded_by' => $finance->id,
                        'amount' => $amountPaid,
                        'currency' => 'ZMW',
                        'method' => $spec['payment'][0],
                        'payment_date' => $spec['payment'][2],
                        'notes' => 'Local demo payment.',
                    ]
                );
                $incomeAccount = $spec['vertical'] === 'furniture' ? '4010' : '4000';

                $this->postJournal('JNL-DEMO-INV-'.$invoice->id, $spec['issue_date'], 'Invoice '.$spec['invoice_number'].' raised', 'invoice', $invoice->id, [
                    ['1100', $total, 0, 'Accounts receivable'],
                    [$incomeAccount, 0, $subtotal, 'Net sales'],
                    ['2100', 0, $taxAmount, 'ZRA VAT 16%'],
                ]);

                $this->postJournal('JNL-DEMO-PAY-'.$payment->id, $spec['payment'][2], 'Payment received for '.$spec['invoice_number'], 'payment', $invoice->id, [
                    [$spec['payment'][0] === 'mobile_money' ? '1020' : '1010', $amountPaid, 0, 'Payment received'],
                    ['1100', 0, $amountPaid, 'Clear accounts receivable'],
                ]);
            } elseif ($spec['status'] === 'overdue') {
                $incomeAccount = $spec['vertical'] === 'furniture' ? '4010' : '4000';
                $this->postJournal('JNL-DEMO-INV-'.$invoice->id, $spec['issue_date'], 'Invoice '.$spec['invoice_number'].' raised', 'invoice', $invoice->id, [
                    ['1100', $total, 0, 'Accounts receivable'],
                    [$incomeAccount, 0, $subtotal, 'Net sales'],
                    ['2100', 0, $taxAmount, 'ZRA VAT 16%'],
                ]);
            }
        }

        $orderDefs = [
            ['MPS-ORD-2026-0001', 'ZESCO Limited', 'QUO-2026-0001', 'bags', 'Branded Laptop Bags', 500, 'production', 65, 'high', $production],
            ['MPS-ORD-2026-0002', 'ABSA Bank Zambia', 'PRO-2026-0002', 'bags', 'Executive Backpacks', 200, 'design', 30, 'normal', $design],
            ['MPS-ORD-2026-0003', 'ZANACO', 'INV-2026-0003', 'furniture', 'Office Chairs & Desks', 40, 'completed', 100, 'normal', $production],
            ['MPS-ORD-2026-0004', 'Airtel Zambia', 'INV-2026-0004', 'bags', 'Travel Bags', 100, 'delivery', 90, 'urgent', $production],
            ['MPS-ORD-2026-0005', 'Chibesa Retail', null, 'interiors', 'Home Fit-Out Consultation', 1, 'brief', 10, 'low', $design],
        ];

        $productionOrders = [];
        foreach ($orderDefs as $def) {
            $productionOrders[$def[0]] = ProductionOrder::firstOrCreate(
                ['order_number' => $def[0]],
                [
                    'client_id' => $clients[$def[1]]->id,
                    'invoice_id' => $def[2] ? $invoices[$def[2]]->id : null,
                    'assigned_to' => $def[9]->id,
                    'vertical' => $def[3],
                    'product_type' => $def[4],
                    'brief' => 'Local demo production order for '.$def[4].'.',
                    'quantity' => $def[5],
                    'stage' => $def[6],
                    'stage_progress' => $def[7],
                    'measurements' => ['width_cm' => 45, 'height_cm' => 35, 'depth_cm' => 12],
                    'fabric_requirements' => ['canvas_metres' => 120, 'lining_metres' => 80],
                    'colour_specs' => ['primary' => 'Navy', 'accent' => 'Copper'],
                    'quoted_price' => 350,
                    'start_date' => today()->subDays(25)->toDateString(),
                    'due_date' => today()->addDays(30)->toDateString(),
                    'completed_date' => $def[6] === 'completed' ? today()->subDays(3)->toDateString() : null,
                    'priority' => $def[8],
                    'qc_passed' => $def[6] === 'completed' ? true : null,
                    'qc_notes' => $def[6] === 'completed' ? 'Passed final quality check.' : null,
                    'internal_notes' => 'Local demo record.',
                ]
            );
        }

        $this->seedChildren('production_stage_logs', function () use ($productionOrders, $production, $now) {
            $progressions = [
                'MPS-ORD-2026-0001' => ['brief', 'design', 'production'],
                'MPS-ORD-2026-0002' => ['brief', 'design'],
                'MPS-ORD-2026-0003' => ['brief', 'design', 'production', 'delivery', 'completed'],
                'MPS-ORD-2026-0004' => ['brief', 'design', 'production', 'delivery'],
                'MPS-ORD-2026-0005' => ['brief'],
            ];
            foreach ($progressions as $number => $stages) {
                $previous = null;
                foreach ($stages as $index => $stage) {
                    ProductionStageLog::create([
                        'order_id' => $productionOrders[$number]->id,
                        'user_id' => $production->id,
                        'from_stage' => $previous ?? 'initial',
                        'to_stage' => $stage,
                        'notes' => 'Moved to '.$stage.'.',
                        'created_at' => $now->copy()->subDays(count($stages) - $index + 2),
                    ]);
                    $previous = $stage;
                }
            }
        });

        $projectDefs = [
            ['MPS-PRJ-2026-001', 'ZANACO', 'ZANACO HQ Boardroom Fit-Out', 'workplace', 'active', 450000, 180000, 45, today()->subDays(30), today()->addDays(60)],
            ['MPS-PRJ-2026-002', 'ABSA Bank Zambia', 'ABSA Lusaka Branch Refresh', 'retail', 'planning', 220000, 12000, 10, today()->subDays(8), today()->addDays(90)],
            ['MPS-PRJ-2026-003', 'ZESCO Limited', 'ZESCO Staff Lounge Interiors', 'workplace', 'completed', 130000, 124000, 100, today()->subDays(120), today()->subDays(10)],
        ];

        $projects = [];
        foreach ($projectDefs as $def) {
            $projects[$def[0]] = Project::firstOrCreate(
                ['project_number' => $def[0]],
                [
                    'client_id' => $clients[$def[1]]->id,
                    'manager_id' => $projectsUser->id,
                    'name' => $def[2],
                    'description' => 'Local demo interior fit-out project.',
                    'location' => 'Lusaka, Zambia',
                    'type' => $def[3],
                    'status' => $def[4],
                    'budget' => $def[5],
                    'actual_cost' => $def[6],
                    'start_date' => $def[8],
                    'end_date' => $def[9],
                    'completion_percent' => $def[7],
                ]
            );
        }

        $this->seedChildren('project_milestones', function () use ($projects, $now) {
            $milestoneDefs = [
                'MPS-PRJ-2026-001' => [
                    ['Site survey', today()->subDays(25), today()->subDays(22), 'completed'],
                    ['Design approval', today()->subDays(15), today()->subDays(12), 'completed'],
                    ['Joinery fabrication', today()->addDays(5), null, 'in_progress'],
                    ['Installation', today()->addDays(35), null, 'pending'],
                    ['Final handover', today()->addDays(58), null, 'pending'],
                ],
                'MPS-PRJ-2026-002' => [
                    ['Initial concept', today()->addDays(10), null, 'pending'],
                    ['Client sign-off', today()->addDays(30), null, 'pending'],
                    ['Fit-out works', today()->addDays(65), null, 'pending'],
                ],
                'MPS-PRJ-2026-003' => [
                    ['Site survey', today()->subDays(115), today()->subDays(112), 'completed'],
                    ['Installation', today()->subDays(60), today()->subDays(40), 'completed'],
                    ['Handover', today()->subDays(12), today()->subDays(10), 'completed'],
                ],
            ];
            foreach ($milestoneDefs as $number => $milestones) {
                foreach ($milestones as $index => $m) {
                    ProjectMilestone::create([
                        'project_id' => $projects[$number]->id,
                        'title' => $m[0],
                        'description' => 'Local demo milestone.',
                        'due_date' => $m[1],
                        'completed_date' => $m[2],
                        'status' => $m[3],
                        'sort_order' => $index,
                        'created_at' => $now->copy()->subDays(30 - $index),
                        'updated_at' => $now,
                    ]);
                }
            }
        });

        $this->seedChildren('project_contractors', function () use ($projects, $now) {
            $contractorDefs = [
                ['MPS-PRJ-2026-001', 'Kabwe Electricals', 'Electrician', '+260 97 2221111', 45000],
                ['MPS-PRJ-2026-001', 'Chitenge Interiors Workshop', 'Carpenter', '+260 96 3334444', 120000],
                ['MPS-PRJ-2026-001', 'Zambia Paint Contractors', 'Painter', '+260 95 5556666', 28000],
                ['MPS-PRJ-2026-002', 'Kabwe Electricals', 'Electrician', '+260 97 2221111', 32000],
            ];
            foreach ($contractorDefs as $c) {
                ProjectContractor::create([
                    'project_id' => $projects[$c[0]]->id,
                    'name' => $c[1],
                    'company' => $c[1],
                    'trade' => $c[2],
                    'phone' => $c[3],
                    'contract_value' => $c[4],
                    'created_at' => $now->copy()->subDays(20),
                    'updated_at' => $now,
                ]);
            }
        });

        $this->seedChildren('project_site_visits', function () use ($projects, $projectsUser, $now) {
            $visitDefs = [
                ['MPS-PRJ-2026-001', today()->subDays(14), 'Boardroom dimensions confirmed; wiring requires revision.', 'on_track'],
                ['MPS-PRJ-2026-001', today()->subDays(2), 'Joinery delays due to timber supply; schedule at risk.', 'delayed'],
                ['MPS-PRJ-2026-002', today()->subDays(3), 'Site clean and ready for concept walkthrough.', 'on_track'],
                ['MPS-PRJ-2026-003', today()->subDays(15), 'Minor snagging list identified and resolved.', 'issue'],
            ];
            foreach ($visitDefs as $v) {
                ProjectSiteVisit::create([
                    'project_id' => $projects[$v[0]]->id,
                    'user_id' => $projectsUser->id,
                    'visit_date' => $v[1],
                    'observations' => $v[2],
                    'photos' => ['visits/'.$v[0].'-site.jpg'],
                    'status' => $v[3],
                    'created_at' => $now->copy()->subDays(10),
                    'updated_at' => $now,
                ]);
            }
        });

        $productCategories = [];
        foreach ([
            ['Backpacks', 'backpacks', 'bags'],
            ['Tote Bags', 'tote-bags', 'bags'],
            ['Home Furniture', 'home-furniture', 'furniture'],
            ['Accessories', 'accessories', 'accessories'],
        ] as $cat) {
            $productCategories[$cat[1]] = ProductCategory::firstOrCreate(
                ['slug' => $cat[1]],
                ['name' => $cat[0], 'vertical' => $cat[2]]
            );
        }

        $productDefs = [
            ['SHOP-BAG-001', 'backpacks', 'Zambia Heritage Backpack', 480, 550, 25, false, true, 'bags', true],
            ['SHOP-BAG-002', 'tote-bags', 'Chitenge Tote Bag', 250, null, 40, false, true, 'bags', false],
            ['SHOP-BAG-003', 'backpacks', 'Executive Leather Laptop Bag', 890, 1050, 8, false, true, 'bags', true],
            ['SHOP-FUR-001', 'home-furniture', 'Handcrafted Lounge Chair', 3200, null, 5, true, true, 'furniture', true],
            ['SHOP-ACC-001', 'accessories', 'Beaded Keyholder', 45, null, 100, false, true, 'accessories', false],
        ];

        $products = [];
        foreach ($productDefs as $p) {
            $products[$p[0]] = Product::firstOrCreate(
                ['sku' => $p[0]],
                [
                    'category_id' => $productCategories[$p[1]]->id,
                    'name' => $p[2],
                    'slug' => strtolower(str_replace(' ', '-', $p[2])),
                    'description' => 'Locally handmade product — local demo listing.',
                    'price' => $p[3],
                    'compare_price' => $p[4],
                    'stock_quantity' => $p[5],
                    'is_custom_order' => $p[6],
                    'is_published' => $p[7],
                    'vertical' => $p[8],
                    'featured' => $p[9],
                    'images' => ['products/'.$p[0].'.jpg'],
                    'variants' => ['colour' => ['Navy', 'Charcoal', 'Natural']],
                    'tags' => ['handmade', 'zambia', $p[8]],
                ]
            );
        }

        $retailDefs = [
            ['Chibesa Mwale', 'chibesa.mwale@gmail.com', '+260 97 1234567', 'Lusaka', 760],
            ['Lubasi Tembo', 'lubasi.tembo@gmail.com', '+260 96 2345678', 'Ndola', 3200],
            ['Mukonde Sichone', 'mukonde.sichone@gmail.com', '+260 95 3456789', 'Kitwe', 110],
        ];

        $retailCustomers = [];
        foreach ($retailDefs as $r) {
            $retailCustomers[$r[1]] = RetailCustomer::firstOrCreate(
                ['email' => $r[1]],
                [
                    'name' => $r[0],
                    'phone' => $r[2],
                    'whatsapp' => $r[2],
                    'delivery_address' => 'Plot 12,'.$r[3].', Zambia',
                    'city' => $r[3],
                    'total_spent' => $r[4],
                ]
            );
        }

        $shopOrderDefs = [
            [
                'MPS-SHOP-2026-0001', 'chibesa.mwale@gmail.com', 'pending', 'pending', null, 30, 'delivery', 'website', $today,
                [['SHOP-BAG-001', 1, 480], ['SHOP-BAG-002', 1, 250]],
            ],
            [
                'MPS-SHOP-2026-0002', 'lubasi.tembo@gmail.com', 'delivered', 'paid', 'mobile_money', 0, 'delivery', 'whatsapp', today()->subDays(6)->toDateString(),
                [['SHOP-FUR-001', 1, 3200]],
            ],
            [
                'MPS-SHOP-2026-0003', 'mukonde.sichone@gmail.com', 'in_production', 'paid', 'mobile_money', 20, 'pickup', 'instagram', today()->subDays(2)->toDateString(),
                [['SHOP-ACC-001', 2, 45]],
            ],
        ];

        foreach ($shopOrderDefs as $def) {
            $subtotal = 0;
            foreach ($def[9] as $line) {
                $subtotal += $line[1] * $line[2];
            }
            $order = EcommerceOrder::firstOrCreate(
                ['order_number' => $def[0]],
                [
                    'customer_id' => $retailCustomers[$def[1]]->id,
                    'status' => $def[2],
                    'subtotal' => $subtotal,
                    'delivery_fee' => $def[5],
                    'total' => $subtotal + $def[5],
                    'payment_status' => $def[3],
                    'payment_method' => $def[4],
                    'delivery_address' => 'Plot 12, Lusaka, Zambia',
                    'delivery_method' => $def[6],
                    'channel' => $def[7],
                    'notes' => 'Local demo shop order.',
                    'created_at' => $def[8].' 09:30:00',
                    'updated_at' => $now,
                ]
            );

            if ($order->items()->count() === 0) {
                foreach ($def[9] as $line) {
                    EcommerceOrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $products[$line[0]]->id,
                        'product_name' => $products[$line[0]]->name,
                        'quantity' => $line[1],
                        'unit_price' => $line[2],
                        'total' => $line[1] * $line[2],
                        'variant_options' => ['colour' => 'Navy'],
                    ]);
                }
            }
        }

        $supplierDefs = [
            ['Zambia Fabrics Ltd', 'fabric', 'sales@zambiafabrics.co.zm', '+260 211 222333', 'TPIN/100011223344', 12500],
            ['Hardware House Zambia', 'hardware', 'orders@hardwarehouse.co.zm', '+260 211 444555', 'TPIN/100022334455', 3200],
            ['Timber Zambia Limited', 'timber', 'sales@timberzambia.co.zm', '+260 211 666777', 'TPIN/100033445566', 8900],
            ['Foam Promotions Zambia', 'other', 'info@foampromotions.co.zm', '+260 211 888999', 'TPIN/100044556677', 0],
            ['ZESCO Utilities', 'services', 'business@zesco.co.zm', '+260 211 123456', 'TPIN/100055667788', 2850],
            ['Neil Transport Services', 'services', 'neil.transport@gmail.com', '+260 97 8887777', 'TPIN/100066778899', 1400],
        ];

        $suppliers = [];
        foreach ($supplierDefs as $s) {
            $suppliers[$s[0]] = Supplier::firstOrCreate(
                ['name' => $s[0]],
                [
                    'company' => $s[0],
                    'email' => $s[2],
                    'phone' => $s[3],
                    'address' => 'Lusaka, Zambia',
                    'tpin' => $s[4],
                    'category' => $s[1],
                    'outstanding_balance' => $s[5],
                    'is_active' => true,
                ]
            );
        }

        $expenseDefs = [
            ['Fabric purchase — Kitenge & Canvas', 'Zambia Fabrics Ltd', '5000', 12500, today()->subDays(12)->toDateString(), 'materials', 'bags', 'bank_transfer'],
            ['Hardware supplies — Zips & Buckles', 'Hardware House Zambia', '5010', 3200, today()->subDays(10)->toDateString(), 'materials', 'bags', 'mobile_money'],
            ['Workshop monthly rent', null, '5110', 9500, today()->subDays(5)->toDateString(), 'rent', 'admin', 'bank_transfer'],
            ['ZESCO electricity supply', 'ZESCO Utilities', '5120', 2850, today()->subDays(4)->toDateString(), 'utilities', 'shared', 'bank_transfer'],
            ['Delivery transport services', 'Neil Transport Services', '5130', 1400, today()->subDays(3)->toDateString(), 'transport', 'shared', 'cash'],
            ['Instagram & Facebook adverts', null, '5140', 1800, today()->subDays(2)->toDateString(), 'marketing', 'admin', 'mobile_money'],
            ['Sewing machine maintenance', null, '5150', 950, today()->subDays(1)->toDateString(), 'maintenance', 'furniture', 'cash'],
        ];

        foreach ($expenseDefs as $index => $e) {
            $existingExpense = Expense::where('title', $e[0])->whereDate('expense_date', $e[4])->first();
            if ($existingExpense) {
                $expense = $existingExpense;
            } else {
                $expense = Expense::create([
                    'recorded_by' => $finance->id,
                    'supplier_id' => $e[1] ? $suppliers[$e[1]]->id : null,
                    'account_id' => $this->account($e[2])->id,
                    'title' => $e[0],
                    'description' => 'Local demo expense.',
                    'amount' => $e[3],
                    'currency' => 'ZMW',
                    'expense_date' => $e[4],
                    'category' => $e[5],
                    'vertical' => $e[6],
                    'payment_method' => $e[7],
                    'is_approved' => true,
                ]);
            }

            $cashAccount = $e[7] === 'cash' ? '1000' : ($e[7] === 'mobile_money' ? '1020' : '1010');
            $this->postJournal('JNL-DEMO-EXP-'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT), $e[4], 'Expense: '.$e[0], 'expense', $expense->id, [
                [$e[2], $e[3], 0, $e[0]],
                [$cashAccount, 0, $e[3], 'Paid by '.$e[7]],
            ]);
        }

        $management = $this->department('Management') ?? Department::first();
        $productionDept = $this->department('Production') ?? $management;
        $designDept = $this->department('Design') ?? $management;
        $salesDept = $this->department('Sales') ?? $management;
        $financeDept = $this->department('Finance') ?? $management;
        $hrDept = $this->department('Human Resources') ?? $management;
        $marketingDept = $this->department('Marketing & Ecommerce') ?? $management;
        $projectsDept = $this->department('Projects & Interiors') ?? $management;

        $employeeDefs = [
            ['MPS-EMP-001', $admin, $management, 'Ruth', 'Kayira', 'Managing Director', 18000, 'admin'],
            ['MPS-EMP-002', $production, $productionDept, 'John', 'Banda', 'Production Manager', 9500, 'bags'],
            ['MPS-EMP-003', $design, $designDept, 'Chanda', 'Mwale', 'Design Lead', 7800, 'bags'],
            ['MPS-EMP-004', $sales, $salesDept, 'Mutale', 'Phiri', 'Sales Executive', 6500, 'shared'],
            ['MPS-EMP-005', $finance, $financeDept, 'Nalishebo', 'Mwamba', 'Accountant', 7200, 'admin'],
            ['MPS-EMP-006', $hr, $hrDept, 'Mwansa', 'Kabwe', 'HR Officer', 6800, 'admin'],
            ['MPS-EMP-007', $marketing, $marketingDept, 'Thandiwe', 'Lungu', 'Marketing Officer', 6200, 'admin'],
            ['MPS-EMP-008', $projectsUser, $projectsDept, 'Mwila', 'Chileshe', 'Projects Manager', 10500, 'interiors'],
            ['MPS-EMP-009', null, $productionDept, 'Bwalya', 'Tembo', 'Tailor', 3800, 'bags'],
            ['MPS-EMP-010', null, $productionDept, 'Sekeli', 'Phiri', 'Upholsterer', 4200, 'furniture'],
        ];

        $employees = [];
        foreach ($employeeDefs as $index => $def) {
            $employees[$def[0]] = Employee::firstOrCreate(
                ['employee_number' => $def[0]],
                [
                    'user_id' => $def[1]?->id,
                    'department_id' => $def[2]->id,
                    'first_name' => $def[3],
                    'last_name' => $def[4],
                    'email' => $def[1]?->email,
                    'phone' => $def[1]?->phone ?? '+260 97 100000'.($index + 1),
                    'national_id' => 'ZN/'.(100000 + $index).'/67',
                    'napsa_number' => 'NAPSA'.(700000 + $index),
                    'nhima_number' => 'NHIMA'.(800000 + $index),
                    'tpin' => 'TPIN/'.(2000000000 + $index),
                    'employment_type' => 'full_time',
                    'job_title' => $def[5],
                    'vertical' => $def[7],
                    'basic_salary' => $def[6],
                    'salary_type' => 'monthly',
                    'hire_date' => today()->subMonths(6 + $index)->toDateString(),
                    'status' => 'active',
                    'address' => 'Lusaka, Zambia',
                    'emergency_contact' => '+260 97 900000'.($index + 1),
                    'bank_name' => 'FNB Zambia',
                    'bank_account' => 'FNB'.(300000 + $index),
                    'mobile_money_number' => '+260 97 100000'.($index + 1),
                ]
            );
        }

        foreach ($employees as $employee) {
            if (Attendance::where('employee_id', $employee->id)->whereDate('date', $today)->exists()) {
                continue;
            }

            $status = $employee->employee_number === 'MPS-EMP-009' ? 'late' : ($employee->employee_number === 'MPS-EMP-010' ? 'leave' : 'present');
            Attendance::create([
                'employee_id' => $employee->id,
                'date' => $today,
                'check_in' => $status === 'present' ? '08:00:00' : ($status === 'late' ? '08:45:00' : null),
                'check_out' => $status === 'present' ? '17:00:00' : null,
                'hours_worked' => $status === 'present' ? 8 : ($status === 'late' ? 7.25 : 0),
                'status' => $status,
                'notes' => 'Local demo attendance.',
                'recorded_by' => $hr->id,
            ]);
        }

        $this->seedChildren('leave_requests', function () use ($employees, $hr, $now) {
            $leaveDefs = [
                ['MPS-EMP-010', 'annual', today(), today()->addDays(4), 5, 'approved', $hr->id],
                ['MPS-EMP-009', 'sick', today()->subDays(2), today()->subDays(1), 2, 'pending', null],
                ['MPS-EMP-003', 'annual', today()->addDays(10), today()->addDays(17), 8, 'pending', null],
            ];
            foreach ($leaveDefs as $l) {
                LeaveRequest::create([
                    'employee_id' => $employees[$l[0]]->id,
                    'approved_by' => $l[6],
                    'type' => $l[1],
                    'start_date' => $l[2],
                    'end_date' => $l[3],
                    'days_requested' => $l[4],
                    'reason' => 'Local demo leave request.',
                    'status' => $l[5],
                    'created_at' => $now->copy()->subDays(6),
                    'updated_at' => $now,
                ]);
            }
        });

        $period = now()->format('Y-m');
        $payslipData = [];
        foreach ($employees as $employee) {
            $allowances = 500;
            $gross = $employee->basic_salary + $allowances;
            $paye = round($gross * 0.15, 2);
            $napsaEmployee = round($gross * 0.05, 2);
            $nhimaEmployee = round($gross * 0.01, 2);
            $payslipData[] = [
                'employee_id' => $employee->id,
                'basic_salary' => $employee->basic_salary,
                'allowances' => $allowances,
                'gross_pay' => $gross,
                'paye_tax' => $paye,
                'napsa_employee' => $napsaEmployee,
                'napsa_employer' => $napsaEmployee,
                'nhima_employee' => $nhimaEmployee,
                'nhima_employer' => $nhimaEmployee,
                'other_deductions' => 0,
                'net_pay' => round($gross - $paye - $napsaEmployee - $nhimaEmployee, 2),
            ];
        }

        $payrollRun = PayrollRun::firstOrCreate(
            ['period' => $period],
            [
                'pay_date' => today()->addDays(5)->toDateString(),
                'status' => 'processed',
                'total_gross' => array_sum(array_column($payslipData, 'gross_pay')),
                'total_paye' => array_sum(array_column($payslipData, 'paye_tax')),
                'total_napsa' => array_sum(array_column($payslipData, 'napsa_employee')),
                'total_nhima' => array_sum(array_column($payslipData, 'nhima_employee')),
                'total_net' => array_sum(array_column($payslipData, 'net_pay')),
                'processed_by' => $finance->id,
            ]
        );

        if ($payrollRun->payslips()->count() === 0) {
            foreach ($payslipData as $data) {
                Payslip::create([
                    'payroll_run_id' => $payrollRun->id,
                    'employee_id' => $data['employee_id'],
                    'basic_salary' => $data['basic_salary'],
                    'allowances' => $data['allowances'],
                    'gross_pay' => $data['gross_pay'],
                    'paye_tax' => $data['paye_tax'],
                    'napsa_employee' => $data['napsa_employee'],
                    'napsa_employer' => $data['napsa_employer'],
                    'nhima_employee' => $data['nhima_employee'],
                    'nhima_employer' => $data['nhima_employer'],
                    'other_deductions' => $data['other_deductions'],
                    'net_pay' => $data['net_pay'],
                    'days_worked' => 22,
                    'days_absent' => 0,
                    'deductions_breakdown' => ['PAYE' => $data['paye_tax'], 'NAPSA' => $data['napsa_employee'], 'NHIMA' => $data['nhima_employee']],
                    'allowances_breakdown' => ['Transport' => $data['allowances']],
                ]);
            }
        }

        $this->postJournal('JNL-DEMO-PAYROLL-'.$payrollRun->id, today()->toDateString(), 'Monthly payroll '.$period, 'payroll', $payrollRun->id, [
            ['5100', $payrollRun->total_gross, 0, 'Gross salaries'],
            ['2110', 0, $payrollRun->total_paye, 'PAYE payable to ZRA'],
            ['2120', 0, $payrollRun->total_napsa, 'NAPSA contributions'],
            ['2130', 0, $payrollRun->total_nhima, 'NHIMA contributions'],
            ['1010', 0, $payrollRun->total_net, 'Net salaries paid'],
        ]);

        $campaignDefs = [
            ['Corporate Bag Outreach — Q4 2026', 'corporate_outreach', 'active', today()->subDays(15), today()->addDays(45), 25000, 8400, ['ZESCO', 'ABSA', 'ZANACO', 'Airtel'], ['leads' => 18, 'conversions' => 4]],
            ['Festive Season Social Campaign', 'social_media', 'active', today()->subDays(7), today()->addDays(30), 12000, 3250, ['Lusaka', 'Ndola', 'Kitwe'], ['impressions' => 24000, 'clicks' => 860, 'conversions' => 32]],
            ['MPS Interior Design Expo 2026', 'event', 'scheduled', today()->addDays(20), today()->addDays(22), 35000, 5000, ['Architects', 'Corporate procurement'], ['registrations' => 45]],
        ];

        $campaigns = [];
        foreach ($campaignDefs as $c) {
            $campaigns[$c[0]] = MarketingCampaign::firstOrCreate(
                ['name' => $c[0]],
                [
                    'created_by' => $marketing->id,
                    'description' => 'Local demo marketing campaign.',
                    'type' => $c[1],
                    'status' => $c[2],
                    'start_date' => $c[3],
                    'end_date' => $c[4],
                    'budget' => $c[5],
                    'actual_spend' => $c[6],
                    'target_audience' => $c[7],
                    'metrics' => $c[8],
                ]
            );
        }

        $this->seedChildren('social_posts', function () use ($campaigns, $marketing, $now) {
            SocialPost::create([
                'campaign_id' => $campaigns['Festive Season Social Campaign']->id,
                'created_by' => $marketing->id,
                'content' => 'Handmade in Zambia. Our new Heritage Backpack and Chitenge Tote collection is here — crafted with local fabric and finished by our Lusaka team. Order yours today!',
                'media' => ['social/heritage-bag.jpg'],
                'platforms' => ['instagram', 'facebook', 'linkedin'],
                'scheduled_at' => $now->copy()->subDays(2),
                'published_at' => $now->copy()->subDays(2),
                'status' => 'published',
                'created_at' => $now->copy()->subDays(4),
                'updated_at' => $now,
            ]);
        });

        $this->seedChildren('leads', function () use ($campaigns, $sales, $projectsUser, $now) {
            $leadDefs = [
                ['Nchimba Sichone', 'Copperbelt Energy', 'nchimba@copperbeltenergy.co.zm', '+260 97 5554321', 'instagram', 'corporate_bags', 'qualified', $campaigns['Festive Season Social Campaign']->id, $sales->id],
                ['Lusaka Hotel Group', 'Lusaka Hotel Group', 'procurement@lusakahotel.co.zm', '+260 211 777888', 'event', 'interiors', 'new', $campaigns['MPS Interior Design Expo 2026']->id, $projectsUser->id],
                ['Doreen Mwila', null, 'doreen.mwila@gmail.com', '+260 96 4443333', 'referral', 'bags', 'converted', $campaigns['Corporate Bag Outreach — Q4 2026']->id, $sales->id],
            ];
            foreach ($leadDefs as $l) {
                Lead::create([
                    'campaign_id' => $l[7],
                    'name' => $l[0],
                    'company' => $l[1],
                    'email' => $l[2],
                    'phone' => $l[3],
                    'source' => $l[4],
                    'interest' => $l[5],
                    'status' => $l[6],
                    'notes' => 'Local demo lead.',
                    'assigned_to' => $l[8],
                    'created_at' => $now->copy()->subDays(8),
                    'updated_at' => $now,
                ]);
            }
        });
    }

    private function seedChildren(string $table, callable $callback): void
    {
        if (DB::table($table)->count() === 0) {
            $callback();
        }
    }

    private function user(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    private function department(string $name): ?Department
    {
        return Department::where('name', $name)->first();
    }

    private function account(string $code): ?ChartOfAccount
    {
        return ChartOfAccount::where('code', $code)->first();
    }

    private function postJournal(string $reference, string $entryDate, string $description, string $source, ?int $sourceId, array $lines): void
    {
        $entry = JournalEntry::firstOrCreate(
            ['reference' => $reference],
            [
                'user_id' => $this->user('finance@myperfectstitch.co.zm')?->id ?? User::first()?->id,
                'entry_date' => $entryDate,
                'description' => $description,
                'source' => $source,
                'source_id' => $sourceId,
            ]
        );

        if ($entry->lines()->count() > 0) {
            return;
        }

        foreach ($lines as $line) {
            JournalLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $this->account($line[0])->id,
                'debit' => $line[1],
                'credit' => $line[2],
                'description' => $line[3] ?? null,
            ]);
        }
    }
}
