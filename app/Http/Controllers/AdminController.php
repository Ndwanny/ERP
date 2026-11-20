<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Symfony\Component\Process\Process;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index');
    }

    // ── Users list ────────────────────────────────────────────────
    public function users()
    {
        return view('admin.users', [
            'users' => User::with('role')->latest()->paginate(20),
        ]);
    }

    // ── Create user form ──────────────────────────────────────────
    public function createUser()
    {
        return view('admin.create-user', ['roles' => Role::orderBy('display_name')->get()]);
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role_id' => ['required', 'exists:roles,id'],
            'department' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['email_verified_at'] = now();

        User::create($data);

        return redirect()->route('admin.users')->with('success', 'User created successfully.');
    }

    // ── Edit user form ────────────────────────────────────────────
    public function editUser(User $user)
    {
        return view('admin.edit-user', [
            'user' => $user->load('role'),
            'roles' => Role::orderBy('display_name')->get(),
        ]);
    }

    public function updateUser(Request $request, User $user)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'role_id' => ['required', 'exists:roles,id'],
            'department' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $data['is_active'] = $request->boolean('is_active', false);

        $user->update($data);

        return redirect()->route('admin.users')->with('success', "User {$user->first_name} {$user->last_name} updated.");
    }

    public function destroyUser(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User removed.');
    }

    // ── Roles ─────────────────────────────────────────────────────
    public function roles()
    {
        $roles = Role::all();
        $all_permissions = [
            'access-invoicing', 'access-crm', 'access-production', 'access-inventory',
            'access-projects', 'access-ecommerce', 'access-accounting', 'access-hr',
            'access-marketing', 'access-analytics',
        ];

        return view('admin.roles', compact('roles', 'all_permissions'));
    }

    public function storeRole(Request $request)
    {
        $request->validate(['name' => 'required|string|max:50|unique:roles,name', 'display_name' => 'required|string|max:100']);
        Role::create(['name' => $request->name, 'display_name' => $request->display_name, 'permissions' => []]);

        return redirect()->route('admin.roles')->with('success', 'Role created.');
    }

    public function updatePermissions(Request $request, Role $role)
    {
        $role->update(['permissions' => $request->input('permissions', [])]);

        return redirect()->route('admin.roles')->with('success', "Permissions updated for {$role->display_name}.");
    }

    // ── Other ─────────────────────────────────────────────────────
    public function auditLogs()
    {
        $logs = AuditLog::with('user')->latest()->paginate(50);

        return view('admin.audit-logs', compact('logs'));
    }

    public function systemSettings()
    {
        $settings = Setting::pluck('value', 'key');

        return view('admin.system', compact('settings'));
    }

    public function updateSystemSettings(Request $request)
    {
        foreach ($request->except('_token', '_method') as $key => $value) {
            Setting::set($key, is_string($value) ? $value : json_encode($value));
        }

        return redirect()->route('admin.system')->with('success', 'Settings saved.');
    }

    public function databaseStatus()
    {
        $connection = config('database.default');
        $driver = DB::getDriverName();

        try {
            $databaseName = DB::connection()->getDatabaseName();
        } catch (\Throwable $e) {
            $databaseName = 'Unavailable';
        }

        $applied = Schema::hasTable('migrations') ? DB::table('migrations')->pluck('migration')->all() : [];
        $files = glob(database_path('migrations/*.php')) ?: [];
        $migrations = [];
        foreach ($files as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            $migrations[] = ['name' => $name, 'ran' => in_array($name, $applied, true)];
        }
        $migrationsRan = count(array_filter($migrations, fn ($m) => $m['ran']));
        $migrationsPending = count($migrations) - $migrationsRan;

        $keyTables = [
            'Users' => 'users',
            'Roles' => 'roles',
            'Audit Logs' => 'audit_logs',
            'Clients' => 'clients',
            'Invoices' => 'invoices',
            'Payments' => 'payments',
            'Production Orders' => 'production_orders',
            'Projects' => 'projects',
            'Inventory Items' => 'inventory_items',
            'Products' => 'products',
            'Ecommerce Orders' => 'ecommerce_orders',
            'Employees' => 'employees',
            'Expenses' => 'expenses',
            'Leads' => 'leads',
        ];
        $tableCounts = [];
        foreach ($keyTables as $label => $table) {
            $tableCounts[] = [
                'label' => $label,
                'table' => $table,
                'rows' => Schema::hasTable($table) ? DB::table($table)->count() : null,
            ];
        }

        $paths = [
            'storage' => storage_path(),
            'storage/framework' => storage_path('framework'),
            'storage/logs' => storage_path('logs'),
            'bootstrap/cache' => base_path('bootstrap/cache'),
        ];
        $storage = [];
        foreach ($paths as $label => $path) {
            $storage[] = ['label' => $label, 'path' => $path, 'writable' => is_writable($path)];
        }
        $storageWritable = ! collect($storage)->contains(fn ($item) => ! $item['writable']);

        $lastBackup = Setting::get('last_backup_at');
        $cacheDriver = config('cache.default');
        $sessionDriver = config('session.driver');
        $queueDriver = config('queue.default');
        $phpVersion = PHP_VERSION;
        $laravelVersion = app()->version();

        return view('admin.database', compact(
            'connection', 'driver', 'databaseName',
            'migrations', 'migrationsRan', 'migrationsPending',
            'tableCounts', 'storage', 'storageWritable',
            'lastBackup', 'cacheDriver', 'sessionDriver', 'queueDriver',
            'phpVersion', 'laravelVersion'
        ));
    }

    public function createBackup()
    {
        try {
            $connection = config('database.default');
            $driver = DB::getDriverName();
            $backupDir = storage_path('backups');

            if (! is_dir($backupDir) && ! mkdir($backupDir, 0755, true) && ! is_dir($backupDir)) {
                return back()->with('error', 'Could not create the backups directory. Check storage permissions.');
            }

            $filename = 'backup-'.now()->format('Y-m-d_His').'.'.($driver === 'sqlite' ? 'sqlite' : 'sql');
            $destination = $backupDir.DIRECTORY_SEPARATOR.$filename;

            if ($driver === 'sqlite') {
                $configured = config('database.connections.sqlite.database');
                $absolute = is_string($configured) && $configured !== ''
                    && ($configured[0] === '/' || preg_match('/^[A-Za-z]:[\\\\\/]/', $configured));
                $source = $absolute ? $configured : database_path('database.sqlite');

                if (! is_file($source)) {
                    return back()->with('error', 'SQLite database file not found at '.$source.'.');
                }
                if (! copy($source, $destination)) {
                    return back()->with('error', 'Failed to copy the SQLite database file.');
                }
            } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
                $probe = new Process(['mysqldump', '--version']);
                $probe->run();
                if (! $probe->isSuccessful()) {
                    return back()->with('error', 'mysqldump is not available on this server. Install the MySQL client tools or use your hosting provider backups.');
                }

                $cfg = config("database.connections.{$connection}", []);
                $optionsFile = tempnam(sys_get_temp_dir(), 'mps-backup-');
                if ($optionsFile === false) {
                    return back()->with('error', 'Could not create a temporary credentials file for the backup.');
                }

                $options = ['[mysqldump]'];
                foreach ([
                    'host' => $cfg['host'] ?? null,
                    'port' => $cfg['port'] ?? null,
                    'user' => $cfg['username'] ?? null,
                    'password' => $cfg['password'] ?? null,
                ] as $key => $value) {
                    if ($value !== null && $value !== '') {
                        $options[] = $key.'="'.str_replace(['\\', '"'], ['\\\\', '\\"'], (string) $value).'"';
                    }
                }
                file_put_contents($optionsFile, implode(PHP_EOL, $options).PHP_EOL);

                $process = new Process([
                    'mysqldump',
                    '--defaults-extra-file='.$optionsFile,
                    '--single-transaction',
                    '--quick',
                    '--result-file='.$destination,
                    (string) ($cfg['database'] ?? ''),
                ]);
                $process->setTimeout(300);
                try {
                    $process->run();
                } finally {
                    @unlink($optionsFile);
                }

                if (! $process->isSuccessful() || ! is_file($destination) || filesize($destination) === 0) {
                    if (is_file($destination)) {
                        @unlink($destination);
                    }
                    $message = trim($process->getErrorOutput());

                    return back()->with('error', 'Database backup failed.'.($message !== '' ? ' '.substr($message, 0, 300) : ''));
                }
            } else {
                return back()->with('error', 'Automatic backups are only supported for SQLite and MySQL/MariaDB connections.');
            }

            Setting::set('last_backup_at', now()->toIso8601String());

            return back()->with('success', 'Database backup created: '.$filename.'.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Backup failed: '.$e->getMessage());
        }
    }
}
