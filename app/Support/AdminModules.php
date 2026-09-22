<?php

namespace App\Support;

/**
 * The list of admin-panel modules a user account can be given access to.
 *
 * Each key is the admin route-name prefix the module owns — module "rates"
 * covers every route named "backend.rates.*". That is what EnsureModuleAccess
 * matches an incoming request against, so adding a module to a group below is
 * all it takes to make it both grantable on the user form and guarded on the
 * way in. Nothing else needs a list of modules.
 *
 * Groups mirror the sidebar headings (see AdminMenu), so the permission grid on
 * the user form reads the same way as the menu the user will end up with.
 */
class AdminModules
{
    public const GROUPS = [
        'App Modules' => [
            'market'       => 'Market Overview',
            'tools'        => 'Financial Tools',
            'goals'        => 'Goal Management',
            'comparison'   => 'Comparison',
            'information'  => 'Information',
            'banking'      => 'Banking Utilities',
            'cyber-safety' => 'Cyber Crime & Help Center',
        ],
        'Audience' => [
            'users' => 'User Management',
        ],
        'System' => [
            'settings' => 'Settings',
        ],
    ];

    /**
     * Modules reserved for the main admin. Deliberately NOT grantable: managing
     * admin accounts and handing out access stays with the one account that owns
     * the panel, so a sub-admin with every module ticked still cannot create
     * other admins.
     */
    public const SUPER_ADMIN_ONLY = ['admin-management'];

    /** Every grantable module key, flat. */
    public static function keys(): array
    {
        return array_keys(static::labels());
    }

    /** Grantable modules as key => label, flat. */
    public static function labels(): array
    {
        return array_merge(...array_values(static::GROUPS));
    }

    public static function label(string $key): string
    {
        return static::labels()[$key] ?? ($key === 'admin-management' ? 'Admin Management' : $key);
    }

    public static function isGrantable(string $key): bool
    {
        return array_key_exists($key, static::labels());
    }

    public static function isSuperAdminOnly(string $key): bool
    {
        return in_array($key, static::SUPER_ADMIN_ONLY, true);
    }

    /**
     * The module a route name belongs to, or null when the route is not gated
     * (the dashboard, login/logout).
     *
     * "backend.settings.general" → "settings", "backend.rates.gold" → "rates".
     */
    public static function forRoute(?string $routeName): ?string
    {
        if (! $routeName || ! str_starts_with($routeName, 'backend.')) {
            return null;
        }

        $key = strtok(substr($routeName, strlen('backend.')), '.');

        return (static::isGrantable($key) || static::isSuperAdminOnly($key)) ? $key : null;
    }
}
