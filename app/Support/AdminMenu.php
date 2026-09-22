<?php

namespace App\Support;

/**
 * The Bfinz admin panel navigation tree — the single source of truth for the
 * sidebar, the routes and the breadcrumbs.
 *
 * Every page in the panel is declared here once. routes/admin.php walks this
 * tree to register a route per page, the sidebar renders it as the menu, and
 * the header derives the breadcrumb from the current route name. Adding a page
 * therefore means adding one entry here — nothing else needs editing.
 *
 * Order follows the way an admin actually works rather than the data model:
 * the app-facing modules (what the user sees in the Bfinz app) come first,
 * then the reference data behind them, then audience and system tooling.
 *
 * Route names follow "backend.{module}.{name}", which is what
 * EnsureModuleAccess matches permissions against: everything under the "market"
 * module is named "backend.market.*". Keep the section's `module` key in step
 * with AdminModules, or the page becomes ungated.
 *
 * `type` selects the view that renders the page:
 *   table      — the standard list screen (search, filters, table, pagination)
 *   calculator — a financial tool's configuration screen + app preview
 *   goal       — a goal template's configuration screen + app preview
 *   analytics  — a charts-and-KPIs report screen
 *   custom     — rendered by the blade named in `view`
 */
class AdminMenu
{
    /**
     * Sidebar groups, in display order. `heading` opens a new labelled band in
     * the menu so sixteen sections stay scannable.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function sections(): array
    {
        return [
            'dashboard' => [
                'label'  => 'Dashboard',
                'icon'   => 'dashboard',
                'module' => 'dashboard',
                'single' => true,
                'children' => [
                    ['name' => 'index', 'label' => 'Dashboard', 'path' => 'dashboard', 'type' => 'custom', 'view' => 'backend.pages.dashboard'],
                ],
            ],

            /* ------------------------------------------------ App modules -- */

            'market' => [
                'heading' => 'App Modules',
                'label'   => 'Market Overview',
                'icon'    => 'trending',
                'module'  => 'market',
                'children' => [
                    ['name' => 'gold',     'label' => 'Gold',             'path' => 'market/gold'],
                    ['name' => 'silver',   'label' => 'Silver',           'path' => 'market/silver'],
                    ['name' => 'currency', 'label' => 'Currency Details', 'path' => 'market/currency'],
                    ['name' => 'fuel',     'label' => 'Fuel Price',       'path' => 'market/fuel'],
                    ['name' => 'fd',       'label' => 'FD Rates',         'path' => 'market/fd-rates'],
                    ['name' => 'rd',       'label' => 'RD Rates',         'path' => 'market/rd-rates'],
                ],
            ],

            'tools' => [
                'label'  => 'Financial Tools',
                'icon'   => 'calculator',
                'module' => 'tools',
                'children' => [
                    ['name' => 'emi',            'label' => 'EMI Calculator',            'path' => 'tools/emi',            'type' => 'calculator'],
                    ['name' => 'lumpsum',        'label' => 'Lumpsum',                   'path' => 'tools/lumpsum',        'type' => 'calculator'],
                    ['name' => 'sip',            'label' => 'SIP',                       'path' => 'tools/sip',            'type' => 'calculator'],
                    ['name' => 'gst',            'label' => 'GST',                       'path' => 'tools/gst',            'type' => 'calculator'],
                    ['name' => 'fd',             'label' => 'FD',                        'path' => 'tools/fd',             'type' => 'calculator'],
                    ['name' => 'rd',             'label' => 'RD',                        'path' => 'tools/rd',             'type' => 'calculator'],
                    ['name' => 'inflation',      'label' => 'Inflation',                 'path' => 'tools/inflation',      'type' => 'calculator'],
                    ['name' => 'retirement',     'label' => 'Retirement',                'path' => 'tools/retirement',     'type' => 'calculator'],
                    ['name' => 'tax',            'label' => 'Tax',                       'path' => 'tools/tax',            'type' => 'calculator'],
                    ['name' => 'net-worth',      'label' => 'Net Worth',                 'path' => 'tools/net-worth',      'type' => 'calculator'],
                    ['name' => 'debt-to-income', 'label' => 'Debt-to-Income Calculator', 'path' => 'tools/debt-to-income', 'type' => 'calculator'],
                ],
            ],

            'goals' => [
                'label'  => 'Goal Management',
                'icon'   => 'target',
                'module' => 'goals',
                'children' => [
                    ['name' => 'home',       'label' => 'Home',       'path' => 'goals/home',       'type' => 'goal'],
                    ['name' => 'education',  'label' => 'Education',  'path' => 'goals/education',  'type' => 'goal'],
                    ['name' => 'vehicle',    'label' => 'Vehicle',    'path' => 'goals/vehicle',    'type' => 'goal'],
                    ['name' => 'marriage',   'label' => 'Marriage',   'path' => 'goals/marriage',   'type' => 'goal'],
                    ['name' => 'retirement', 'label' => 'Retirement', 'path' => 'goals/retirement', 'type' => 'goal'],
                    ['name' => 'custom',     'label' => 'Custom Goal', 'path' => 'goals/custom',    'type' => 'goal'],
                ],
            ],

            'comparison' => [
                'label'  => 'Comparison',
                'icon'   => 'compare',
                'module' => 'comparison',
                'children' => [
                    ['name' => 'savings', 'label' => 'Savings Account Comparison', 'path' => 'comparison/savings-accounts'],
                    ['name' => 'loans',   'label' => 'Loan Comparison',            'path' => 'comparison/loans'],
                ],
            ],

            'information' => [
                'label'  => 'Information',
                'icon'   => 'info',
                'module' => 'information',
                'children' => [
                    ['name' => 'rbi',        'label' => 'RBI Information',       'path' => 'information/rbi'],
                    ['name' => 'ombudsman',  'label' => 'RBI Ombudsman',         'path' => 'information/ombudsman'],
                    ['name' => 'financial',  'label' => 'Financial Information', 'path' => 'information/financial'],
                    ['name' => 'compliance', 'label' => 'Compliance Corner',     'path' => 'information/compliance'],
                    ['name' => 'economic',   'label' => 'Economic Dashboard',    'path' => 'information/economic'],
                ],
            ],

            'banking' => [
                'label'  => 'Banking Utilities',
                'icon'   => 'bank',
                'module' => 'banking',
                'children' => [
                    ['name' => 'holidays',  'label' => 'Banking Holidays',    'path' => 'banking/holidays'],
                    ['name' => 'atms',      'label' => 'ATM Locator',         'path' => 'banking/atm-locator'],
                    ['name' => 'ifsc',      'label' => 'IFSC & MICR Search',  'path' => 'banking/ifsc-micr'],
                    ['name' => 'branches',  'label' => 'Bank Branch Locator', 'path' => 'banking/branch-locator'],
                    ['name' => 'forms',     'label' => 'Banking Forms',       'path' => 'banking/forms'],
                    ['name' => 'cheque',    'label' => 'Cheque Guidance',     'path' => 'banking/cheque-guidance'],
                    ['name' => 'directory', 'label' => 'Bank Directory',      'path' => 'banking/directory'],
                ],
            ],

            'cyber-safety' => [
                'label'  => 'Cyber Crime & Help Center',
                'icon'   => 'shield',
                'module' => 'cyber-safety',
                'children' => [
                    ['name' => 'help',            'label' => 'Cyber Crime Help', 'path' => 'cyber-safety/cyber-crime-help'],
                    ['name' => 'fraud-awareness', 'label' => 'Fraud Awareness',  'path' => 'cyber-safety/fraud-awareness'],
                    ['name' => 'banking-safety',  'label' => 'Banking Safety',   'path' => 'cyber-safety/banking-safety'],
                    ['name' => 'scam-awareness',  'label' => 'Scam Awareness',   'path' => 'cyber-safety/scam-awareness'],
                    ['name' => 'help-center',     'label' => 'Help Center',      'path' => 'cyber-safety/help-center'],
                ],
            ],

            /* -------------------------------------------------- Audience -- */

            'users' => [
                'heading' => 'Audience',
                'label'   => 'User Management',
                'icon'    => 'users',
                'module'  => 'users',
                'children' => [
                    ['name' => 'index',    'label' => 'Users',            'path' => 'users'],
                    ['name' => 'activity', 'label' => 'User Activity',    'path' => 'users/activity'],
                    ['name' => 'feedback', 'label' => 'User Feedback',    'path' => 'users/feedback'],
                    ['name' => 'support',  'label' => 'Support Requests', 'path' => 'users/support'],
                    // Reached from the Users table, not the sidebar.
                    ['name' => 'show', 'label' => 'User Details', 'path' => 'users/details', 'type' => 'custom', 'view' => 'backend.pages.user-details', 'hidden' => true],
                ],
            ],

            /* ---------------------------------------------------- System -- */

            'admin-management' => [
                'heading' => 'System',
                'label'   => 'Admin Management',
                'icon'    => 'shield-user',
                'module'  => 'admin-management',
                'children' => [
                    ['name' => 'admins',        'label' => 'Admin Users',         'path' => 'admin-management/users'],
                    ['name' => 'roles',         'label' => 'Roles & Permissions', 'path' => 'admin-management/roles', 'type' => 'custom', 'view' => 'backend.pages.roles'],
                    ['name' => 'activity-logs', 'label' => 'Activity Logs',       'path' => 'admin-management/activity-logs'],
                ],
            ],

            'settings' => [
                'label'  => 'Settings',
                'icon'   => 'settings',
                'module' => 'settings',
                'children' => [
                    ['name' => 'general',    'label' => 'General Settings',   'path' => 'settings/general',    'type' => 'custom', 'view' => 'backend.pages.settings-general'],
                    ['name' => 'app',        'label' => 'App Settings',       'path' => 'settings/app',        'type' => 'custom', 'view' => 'backend.pages.settings-app'],
                    ['name' => 'sms',        'label' => 'SMS / OTP API',      'path' => 'settings/sms',        'type' => 'custom', 'view' => 'backend.pages.settings-sms'],
                    ['name' => 'features',   'label' => 'Feature Controls',   'path' => 'settings/features',   'type' => 'custom', 'view' => 'backend.pages.settings-features'],
                    ['name' => 'privacy',    'label' => 'Privacy Policy',     'path' => 'settings/privacy',    'type' => 'custom', 'view' => 'backend.pages.settings-legal'],
                    ['name' => 'terms',      'label' => 'Terms & Conditions', 'path' => 'settings/terms',      'type' => 'custom', 'view' => 'backend.pages.settings-legal'],
                    ['name' => 'disclaimer', 'label' => 'Disclaimer',         'path' => 'settings/disclaimer', 'type' => 'custom', 'view' => 'backend.pages.settings-legal'],
                ],
            ],
        ];
    }

    /**
     * Every page, keyed by its full route name.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function pages(): array
    {
        static $pages = null;

        if ($pages !== null) {
            return $pages;
        }

        $pages = [];

        foreach (static::sections() as $sectionKey => $section) {
            foreach ($section['children'] as $child) {
                $route = 'backend.' . $section['module'] . '.' . $child['name'];

                // The dashboard keeps its historic "backend.dashboard" name.
                if ($sectionKey === 'dashboard') {
                    $route = 'backend.dashboard';
                }

                $pages[$route] = $child + [
                    'route'         => $route,
                    'type'          => 'table',
                    'section'       => $sectionKey,
                    'section_label' => $section['label'],
                    'module'        => $section['module'],
                    'icon'          => $section['icon'],
                    'hidden'        => false,
                ];
            }
        }

        return $pages;
    }

    /** One page by route name, or null. */
    public static function page(?string $route): ?array
    {
        return $route ? (static::pages()[$route] ?? null) : null;
    }

    /**
     * Breadcrumb trail for a route, as [label, url|null] pairs.
     *
     * "Dashboard / Market Overview / Gold" — the section is not a link because
     * a section has no page of its own; only its children do.
     */
    public static function breadcrumb(?string $route): array
    {
        $page = static::page($route);

        if (! $page) {
            return [['label' => 'Dashboard', 'url' => route('backend.dashboard')]];
        }

        if ($page['section'] === 'dashboard') {
            return [['label' => 'Dashboard', 'url' => null]];
        }

        return [
            ['label' => 'Dashboard', 'url' => route('backend.dashboard')],
            ['label' => $page['section_label'], 'url' => null],
            ['label' => $page['label'], 'url' => null],
        ];
    }

    /** True when this route belongs to the given sidebar section. */
    public static function sectionIsActive(string $sectionKey, ?string $route): bool
    {
        return (static::page($route)['section'] ?? null) === $sectionKey;
    }
}
