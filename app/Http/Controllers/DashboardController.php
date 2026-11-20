<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Client;
use App\Models\EcommerceOrder;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\LeaveRequest;
use App\Models\MarketingCampaign;
use App\Models\PayrollRun;
use App\Models\ProductionOrder;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\ProjectSiteVisit;
use App\Models\Setting;
use App\Models\SocialPost;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $allStats = $this->getAllStats();
        $keys = $this->visibleStatKeys($user);
        $stats = array_intersect_key($allStats, array_flip($keys));
        $statCards = $this->buildStatCards($stats, $keys);
        $modules = $this->getAccessibleModules($allStats, $user);
        $system = $this->getSystemStatus();

        return view('dashboard', compact('stats', 'statCards', 'modules', 'user', 'system'));
    }

    public function stats()
    {
        $allStats = $this->getAllStats();
        $keys = $this->visibleStatKeys(auth()->user());
        $stats = array_intersect_key($allStats, array_flip($keys));
        $stats['timestamp'] = now()->toIso8601String();

        return response()->json($stats);
    }

    private function getAllStats(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'active_orders' => ProductionOrder::whereNotIn('stage', ['completed', 'cancelled'])->count(),
            'orders_due_today' => ProductionOrder::whereNotIn('stage', ['completed', 'cancelled'])
                ->whereDate('due_date', $today)->count(),
            'completed_orders' => ProductionOrder::where('stage', 'completed')->count(),
            'monthly_revenue' => Invoice::where('status', 'paid')
                ->where('paid_at', '>=', $thisMonth)->sum('total'),
            'staff_on_duty' => Attendance::whereDate('date', $today)
                ->where('status', 'present')->count(),
            'total_staff' => Employee::where('status', 'active')->count(),
            'pending_invoices' => Invoice::whereIn('status', ['sent', 'viewed', 'partial'])->count(),
            'overdue_invoices' => Invoice::where('status', '!=', 'paid')
                ->whereDate('due_date', '<', $today)->count(),
            'new_shop_orders' => EcommerceOrder::where('status', 'pending')
                ->where('created_at', '>=', $today)->count(),
            'low_stock_items' => InventoryItem::where('status', 'low_stock')->count(),
            'active_clients' => Client::where('status', 'active')->count(),
            'active_campaigns' => MarketingCampaign::where('status', 'active')->count(),
            'new_leads' => Lead::where('status', 'new')->count(),
            'scheduled_posts' => SocialPost::where('status', 'scheduled')->count(),
            'active_projects' => Project::where('status', 'active')->count(),
            'overdue_milestones' => ProjectMilestone::whereNotIn('status', ['completed'])
                ->whereDate('due_date', '<', $today)->count(),
            'site_visits_this_month' => ProjectSiteVisit::whereDate('visit_date', '>=', $thisMonth)->count(),
            'expenses_this_month' => Expense::whereDate('expense_date', '>=', $thisMonth)->sum('amount'),
            'pending_leave' => LeaveRequest::where('status', 'pending')->count(),
            'latest_payroll' => PayrollRun::latest('period')->value('period'),
        ];
    }

    private function visibleStatKeys($user): array
    {
        if ($user->hasRole('super-admin') || $user->hasRole('manager')) {
            return ['active_orders', 'monthly_revenue', 'staff_on_duty', 'total_staff', 'pending_invoices', 'overdue_invoices'];
        }
        if ($user->hasRole('production') || $user->hasRole('design')) {
            return ['active_orders', 'orders_due_today', 'completed_orders', 'low_stock_items'];
        }
        if ($user->hasRole('sales')) {
            return ['pending_invoices', 'overdue_invoices', 'monthly_revenue', 'active_clients', 'new_shop_orders'];
        }
        if ($user->hasRole('marketing')) {
            return ['active_campaigns', 'new_leads', 'scheduled_posts', 'new_shop_orders'];
        }
        if ($user->hasRole('accounts')) {
            return ['monthly_revenue', 'pending_invoices', 'overdue_invoices', 'expenses_this_month'];
        }
        if ($user->hasRole('hr-manager')) {
            return ['staff_on_duty', 'total_staff', 'pending_leave', 'latest_payroll'];
        }
        if ($user->hasRole('projects')) {
            return ['active_projects', 'overdue_milestones', 'site_visits_this_month', 'active_clients'];
        }

        return ['active_orders'];
    }

    private function statCardDefinitions(): array
    {
        return [
            'active_orders' => ['label' => 'Active Orders', 'sub' => 'In production', 'accent' => 'accent-navy'],
            'orders_due_today' => ['label' => 'Orders Due Today', 'sub' => 'Delivery schedule', 'accent' => 'accent-navy'],
            'completed_orders' => ['label' => 'Completed Orders', 'sub' => 'All time', 'accent' => 'accent-success'],
            'monthly_revenue' => ['label' => 'Revenue (ZMW)', 'sub' => 'This month', 'accent' => 'accent-success', 'size' => 'font-size:22px;font-weight:700;'],
            'staff_on_duty' => ['label' => 'Staff on Duty', 'sub' => 'Today', 'accent' => '', 'ratio' => 'total_staff'],
            'total_staff' => ['label' => 'Active Staff', 'sub' => 'On payroll', 'accent' => ''],
            'pending_invoices' => ['label' => 'Pending Invoices', 'sub' => 'Awaiting payment', 'accent' => 'accent-error', 'color' => 'var(--error)'],
            'overdue_invoices' => ['label' => 'Overdue Invoices', 'sub' => 'Past due date', 'accent' => 'accent-error'],
            'new_shop_orders' => ['label' => 'New Shop Orders', 'sub' => 'Today', 'accent' => 'accent-success'],
            'low_stock_items' => ['label' => 'Low Stock Items', 'sub' => 'Need reordering', 'accent' => 'accent-error'],
            'active_clients' => ['label' => 'Active Clients', 'sub' => 'Accounts', 'accent' => 'accent-navy'],
            'active_campaigns' => ['label' => 'Active Campaigns', 'sub' => 'Running now', 'accent' => 'accent-success'],
            'new_leads' => ['label' => 'New Leads', 'sub' => 'Awaiting contact', 'accent' => 'accent-navy'],
            'scheduled_posts' => ['label' => 'Scheduled Posts', 'sub' => 'Social calendar', 'accent' => 'accent-success'],
            'active_projects' => ['label' => 'Active Projects', 'sub' => 'In progress', 'accent' => 'accent-navy'],
            'overdue_milestones' => ['label' => 'Overdue Milestones', 'sub' => 'Need attention', 'accent' => 'accent-error'],
            'site_visits_this_month' => ['label' => 'Site Visits', 'sub' => 'This month', 'accent' => 'accent-success'],
            'expenses_this_month' => ['label' => 'Expenses (ZMW)', 'sub' => 'This month', 'accent' => 'accent-error', 'size' => 'font-size:22px;font-weight:700;'],
            'pending_leave' => ['label' => 'Pending Leave', 'sub' => 'Awaiting approval', 'accent' => 'accent-error'],
            'latest_payroll' => ['label' => 'Latest Payroll', 'sub' => 'Period', 'accent' => 'accent-navy'],
        ];
    }

    private function buildStatCards(array $stats, array $keys): array
    {
        $definitions = $this->statCardDefinitions();
        $cards = [];

        foreach ($keys as $key) {
            if (! isset($definitions[$key])) {
                continue;
            }
            $definition = $definitions[$key];
            $value = $stats[$key] ?? 0;
            $display = in_array($key, ['monthly_revenue', 'expenses_this_month'], true)
                ? number_format($value, 0)
                : (string) $value;

            if (isset($definition['ratio'])) {
                $display = $value.' / '.($stats[$definition['ratio']] ?? 0);
            }
            if ($key === 'latest_payroll' && ! $value) {
                $display = '—';
            }

            $cards[] = [
                'key' => $key,
                'label' => $definition['label'],
                'display' => $display,
                'sub' => $key === 'pending_invoices'
                    ? ($stats['overdue_invoices'] ?? 0).' overdue'
                    : $definition['sub'],
                'accent' => $definition['accent'] ?? '',
                'style' => trim(($definition['size'] ?? '').' '.((isset($definition['color'])) ? 'color:'.$definition['color'].';' : '')),
                'ratio_key' => $definition['ratio'] ?? null,
                'format' => in_array($key, ['monthly_revenue', 'expenses_this_month'], true) ? 'number' : 'text',
            ];
        }

        return $cards;
    }

    private function getAccessibleModules(array $stats, $user): array
    {
        $allModules = [
            ['id' => 'invoicing', 'name' => 'Smart Invoicing', 'route' => 'invoicing.index', 'icon' => '🧾', 'permission' => 'access-invoicing', 'group' => 'Core Operations', 'description' => 'Quotes, proforma invoices, VAT receipts & client payment tracking.', 'badge' => $stats['pending_invoices'].' Pending', 'badge_color' => '#e65100', 'badge_bg' => '#fff3e0', 'icon_bg' => '#fff3e0', 'keywords' => 'smart invoicing quotes payments receipts vat'],
            ['id' => 'production', 'name' => 'Order & Production', 'route' => 'production.index', 'icon' => '🏭', 'permission' => 'access-production', 'group' => 'Core Operations', 'description' => 'Track every order from Brief → Design → Production → QC → Delivery.', 'badge' => $stats['active_orders'].' Active', 'badge_color' => '#2e7d32', 'badge_bg' => '#e8f5e9', 'icon_bg' => '#e8f5e9', 'keywords' => 'order production manufacturing bags furniture interiors'],
            ['id' => 'inventory', 'name' => 'Inventory', 'route' => 'inventory.index', 'icon' => '🧵', 'permission' => 'access-inventory', 'group' => 'Core Operations', 'description' => 'Manage fabric, chitenge, leather, foam & materials across all verticals.', 'badge' => $stats['low_stock_items'].' Low Stock', 'badge_color' => '#b37a1a', 'badge_bg' => 'rgba(249,176,64,0.15)', 'icon_bg' => 'rgba(249,176,64,0.12)', 'keywords' => 'inventory stock fabric materials chitenge leather foam'],
            ['id' => 'crm', 'name' => 'CRM', 'route' => 'crm.index', 'icon' => '🤝', 'permission' => 'access-crm', 'group' => 'Core Operations', 'description' => 'Institutional client accounts, interactions and sales pipeline management.', 'badge' => 'Clients', 'badge_color' => '#4527a0', 'badge_bg' => '#ede7f6', 'icon_bg' => '#ede7f6', 'keywords' => 'crm clients customers corporate institutional relationships'],
            ['id' => 'projects', 'name' => 'Project Management', 'route' => 'projects.index', 'icon' => '📐', 'permission' => 'access-projects', 'group' => 'Core Operations', 'description' => 'Interior fit-out projects, milestones, contractors, site visits & timeline tracking.', 'badge' => 'Projects', 'badge_color' => '#1565c0', 'badge_bg' => '#e3f2fd', 'icon_bg' => '#e3f2fd', 'keywords' => 'projects interior fitout milestones timeline contractors'],
            ['id' => 'ecommerce', 'name' => 'Ecommerce', 'route' => 'ecommerce.index', 'icon' => '🛍️', 'permission' => 'access-ecommerce', 'group' => 'Core Operations', 'description' => 'Online store — products, orders, delivery and retail customer experience.', 'badge' => $stats['new_shop_orders'].' New Orders', 'badge_color' => '#2e7d32', 'badge_bg' => '#e8f5e9', 'icon_bg' => '#e8f5e9', 'keywords' => 'ecommerce shop online store products bags furniture'],
            ['id' => 'accounting', 'name' => 'Accounting', 'route' => 'accounting.index', 'icon' => '📊', 'permission' => 'access-accounting', 'group' => 'Finance & People', 'description' => 'Income, expenses, supplier payments, ledger and ZRA reports.', 'badge' => now()->format('M Y'), 'badge_color' => '#b37a1a', 'badge_bg' => 'rgba(249,176,64,0.15)', 'icon_bg' => 'rgba(249,176,64,0.12)', 'keywords' => 'accounting finance books expenses revenue ledger zra'],
            ['id' => 'hr', 'name' => 'HR & Payroll', 'route' => 'hr.index', 'icon' => '👔', 'permission' => 'access-hr', 'group' => 'Finance & People', 'description' => 'Staff records, attendance, leave management & monthly payroll with PAYE/NAPSA/NHIMA.', 'badge' => $stats['total_staff'].' Employees', 'badge_color' => '#6a1b9a', 'badge_bg' => '#f3e5f5', 'icon_bg' => '#f3e5f5', 'keywords' => 'hr human resource payroll staff attendance leave salary napsa nhima'],
            ['id' => 'marketing', 'name' => 'Marketing', 'route' => 'marketing.index', 'icon' => '📣', 'permission' => 'access-marketing', 'group' => 'Growth & Visibility', 'description' => 'Campaigns, social media scheduler, events & corporate outreach tools.', 'badge' => 'Campaigns', 'badge_color' => '#880e4f', 'badge_bg' => '#fce4ec', 'icon_bg' => '#fce4ec', 'keywords' => 'marketing campaigns social media promotions events outreach'],
            ['id' => 'analytics', 'name' => 'Analytics', 'route' => 'analytics.index', 'icon' => '📈', 'permission' => 'access-analytics', 'group' => 'Growth & Visibility', 'description' => 'Business intelligence — revenue, production output, top clients & performance trends.', 'badge' => 'Live Data', 'badge_color' => '#004d40', 'badge_bg' => '#e0f2f1', 'icon_bg' => '#e0f2f1', 'keywords' => 'analytics reports business intelligence dashboard metrics'],
            ['id' => 'admin', 'name' => 'Super Admin', 'route' => 'admin.index', 'icon' => '🔐', 'permission' => 'role:super-admin', 'group' => 'System & Communications', 'description' => 'User roles, permissions, module access control, audit logs & system configuration.', 'badge' => 'Admin', 'badge_color' => '#b71c1c', 'badge_bg' => '#ffebee', 'icon_bg' => '#ffebee', 'keywords' => 'admin super administrator roles permissions users system settings'],
            ['id' => 'slack', 'name' => 'Slack', 'route' => null, 'url' => 'https://slack.com', 'icon' => '💬', 'permission' => null, 'group' => 'System & Communications', 'description' => 'Internal team communication — channels for production, design, sales & management.', 'badge' => 'External ↗', 'badge_color' => '#283593', 'badge_bg' => '#e8eaf6', 'icon_bg' => '#e8eaf6', 'keywords' => 'slack communication chat messaging team'],
        ];

        $accessible = array_filter($allModules, function ($module) use ($user) {
            if ($module['permission'] === null) {
                return true;
            }
            if (str_starts_with($module['permission'], 'role:')) {
                return $user->hasRole(substr($module['permission'], 5));
            }

            return $user->can($module['permission']);
        });

        return array_values($accessible);
    }

    private function getSystemStatus(): array
    {
        $database = true;
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $database = false;
        }

        return [
            'database' => $database,
            'web_server' => true,
            'file_storage' => is_writable(storage_path()) && is_writable(base_path('bootstrap/cache')),
            'email' => config('mail.default') !== 'array',
            'queue' => true,
            'driver' => strtoupper(config('database.default')),
            'cache' => strtoupper(config('cache.default')),
            'last_backup' => Setting::get('last_backup_at'),
        ];
    }
}
