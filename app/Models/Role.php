<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public const ACCESS_INVOICING = 'access-invoicing';

    public const ACCESS_PRODUCTION = 'access-production';

    public const ACCESS_INVENTORY = 'access-inventory';

    public const ACCESS_CRM = 'access-crm';

    public const ACCESS_PROJECTS = 'access-projects';

    public const ACCESS_ECOMMERCE = 'access-ecommerce';

    public const ACCESS_ACCOUNTING = 'access-accounting';

    public const ACCESS_HR = 'access-hr';

    public const ACCESS_MARKETING = 'access-marketing';

    public const ACCESS_ANALYTICS = 'access-analytics';

    public const ALL_PERMISSIONS = [
        self::ACCESS_INVOICING,
        self::ACCESS_PRODUCTION,
        self::ACCESS_INVENTORY,
        self::ACCESS_CRM,
        self::ACCESS_PROJECTS,
        self::ACCESS_ECOMMERCE,
        self::ACCESS_ACCOUNTING,
        self::ACCESS_HR,
        self::ACCESS_MARKETING,
        self::ACCESS_ANALYTICS,
    ];

    public const ROLE_PERMISSIONS = [
        'super-admin' => self::ALL_PERMISSIONS,
        'manager' => self::ALL_PERMISSIONS,
        'production' => [self::ACCESS_PRODUCTION, self::ACCESS_INVENTORY],
        'design' => [self::ACCESS_PRODUCTION, self::ACCESS_INVENTORY],
        'sales' => [self::ACCESS_CRM, self::ACCESS_INVOICING, self::ACCESS_ECOMMERCE],
        'marketing' => [self::ACCESS_MARKETING, self::ACCESS_CRM, self::ACCESS_ECOMMERCE],
        'accounts' => [self::ACCESS_INVOICING, self::ACCESS_ACCOUNTING, self::ACCESS_ANALYTICS],
        'hr-manager' => [self::ACCESS_HR],
        'projects' => [self::ACCESS_PROJECTS, self::ACCESS_CRM, self::ACCESS_INVENTORY],
    ];

    public const DISPLAY_NAMES = [
        'super-admin' => 'Super Administrator',
        'manager' => 'Manager',
        'production' => 'Production Staff',
        'design' => 'Design Staff',
        'sales' => 'Sales',
        'marketing' => 'Marketing',
        'accounts' => 'Accounts',
        'hr-manager' => 'HR Manager',
        'projects' => 'Projects & Interiors',
    ];

    protected $fillable = ['name', 'display_name', 'permissions'];

    protected $casts = ['permissions' => 'array'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }
}
