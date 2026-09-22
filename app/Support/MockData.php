<?php

namespace App\Support;

/**
 * Static content for the admin screens that are not plain tables — the
 * dashboard, the analytics reports, the calculator and goal configuration
 * pages, API status and the role matrix.
 *
 * Like MockTables, this is deliberately the only place the numbers live, so the
 * views stay presentation-only and each method can later be replaced by a real
 * query or API call without touching blade.
 */
class MockData
{
    /* ------------------------------------------------------- Dashboard -- */

    /**
     * The four headline counters at the top of the dashboard.
     *
     * The first card is the accent one, and the last carries a sparkline.
     *
     * `change`/`direction`/`caption` are kept but not currently rendered — the
     * card's trend row was removed from the design. Restoring it is a matter of
     * passing them back through from dashboard.blade.php.
     */
    public static function dashboardStats(): array
    {
        return [
            [
                'label' => 'Total Users', 'value' => '12,540',
                'change' => '+12.5%', 'direction' => 'up', 'caption' => 'vs last month',
                'icon' => 'users', 'variant' => 'primary',
                'route' => 'backend.users.index',
            ],
            [
                'label' => 'Active Users', 'value' => '8,420',
                'change' => '+8.4%', 'direction' => 'up', 'caption' => 'vs last month',
                'icon' => 'user-check', 'variant' => 'plain',
                'route' => 'backend.users.activity',
            ],
            [
                'label' => 'Total Calculations', 'value' => '35,420',
                'change' => '+18.2%', 'direction' => 'up', 'caption' => 'this month',
                'icon' => 'calculator', 'variant' => 'royal',
                'route' => 'backend.tools.emi',
            ],
            [
                'label' => 'Loan Comparisons', 'value' => '4,280',
                'change' => '+10.6%', 'direction' => 'up', 'caption' => 'this month',
                'icon' => 'git-compare', 'variant' => 'tint',
                'route' => 'backend.comparison.loans',
                'spark' => [22, 25, 24, 29, 27, 32, 35, 33, 39, 43, 46, 51],
            ],
        ];
    }

    /**
     * "Current Statistic" — the nested-arc gauge under the KPI row.
     *
     * The percentages are independent, not parts of a whole: each is the share
     * of active users who touched that area of the app in the last 30 days.
     * That is why they do not sum to 100, and why the chart is concentric arcs
     * rather than a pie.
     */
    public static function currentStatistic(): array
    {
        return [
            ['label' => 'Calculators',  'percent' => 66, 'value' => '35,420', 'colour' => '#0066FF'],
            ['label' => 'Rate Views',   'percent' => 50, 'value' => '54,570', 'colour' => '#061A5C'],
            ['label' => 'Comparisons',  'percent' => 11, 'value' => '4,280',  'colour' => '#F59E0B'],
            ['label' => 'Goal Plans',   'percent' => 22, 'value' => '6,840',  'colour' => '#16A34A'],
        ];
    }

    /**
     * "Market Overview" — weekly views of each market rate screen.
     *
     * Views rather than the rates themselves: gold at ₹6,850 and silver at
     * ₹92,400 share no sensible axis, but how often each screen is opened does.
     */
    public static function marketOverview(): array
    {
        return [
            'labels' => ['Week 01', 'Week 02', 'Week 03', 'Week 04', 'Week 05',
                         'Week 06', 'Week 07', 'Week 08', 'Week 09', 'Week 10'],
            'highlight' => ['index' => 7, 'series' => 0, 'label' => '8.2k views', 'sub' => 'Week 08 · Gold'],
            'series' => [
                ['name' => 'Gold',      'colour' => '#F59E0B', 'on' => true,
                 'values' => [4200, 5100, 4800, 6300, 5900, 7400, 6800, 8200, 7600, 8900]],
                ['name' => 'Silver',    'colour' => '#0066FF', 'on' => true,
                 'values' => [6800, 6200, 7100, 5400, 6600, 4900, 5800, 5100, 6400, 7900]],
                ['name' => 'USD / INR', 'colour' => '#1747C8', 'on' => false,
                 'values' => [3100, 3400, 3200, 3900, 3600, 4100, 3800, 4400, 4200, 4700]],
                ['name' => 'Petrol',    'colour' => '#64748B', 'on' => false,
                 'values' => [2600, 2900, 2700, 3200, 3000, 3500, 3300, 3700, 3500, 3900]],
            ],
        ];
    }

    /** "Most used financial tools" ranking cards. */
    public static function topTools(): array
    {
        return [
            ['rank' => 1, 'name' => 'EMI Calculator',       'uses' => '12,480', 'share' => 35, 'change' => '+14.2%', 'direction' => 'up'],
            ['rank' => 2, 'name' => 'SIP Calculator',       'uses' => '9,240',  'share' => 26, 'change' => '+9.8%',  'direction' => 'up'],
            ['rank' => 3, 'name' => 'FD Calculator',        'uses' => '3,640',  'share' => 10, 'change' => '+4.1%',  'direction' => 'up'],
            ['rank' => 4, 'name' => 'Tax Calculator',       'uses' => '3,120',  'share' => 9,  'change' => '-1.6%',  'direction' => 'down'],
            ['rank' => 5, 'name' => 'Retirement Calculator', 'uses' => '2,410', 'share' => 7,  'change' => '+6.3%',  'direction' => 'up'],
        ];
    }

    /** "Most viewed rates" list. */
    public static function mostViewedRates(): array
    {
        return [
            ['name' => 'Gold',      'detail' => '24K · ₹6,850 / 10g', 'views' => '18,420', 'share' => 92, 'change' => '+2.1%',  'direction' => 'up'],
            ['name' => 'Silver',    'detail' => '₹92,400 / kg',       'views' => '9,180',  'share' => 58, 'change' => '+1.4%',  'direction' => 'up'],
            ['name' => 'USD / INR', 'detail' => '₹83.21',             'views' => '7,640',  'share' => 46, 'change' => '-0.3%',  'direction' => 'down'],
            ['name' => 'Petrol',    'detail' => 'Chennai · ₹102.63',  'views' => '6,910',  'share' => 41, 'change' => '+3.8%',  'direction' => 'up'],
            ['name' => 'Diesel',    'detail' => 'Chennai · ₹94.24',   'views' => '5,240',  'share' => 32, 'change' => '+2.9%',  'direction' => 'up'],
            ['name' => 'FD Rates',  'detail' => 'Up to 7.85%',        'views' => '4,870',  'share' => 28, 'change' => '+5.2%',  'direction' => 'up'],
            ['name' => 'RD Rates',  'detail' => 'Up to 7.10%',        'views' => '2,310',  'share' => 14, 'change' => '+0.8%',  'direction' => 'up'],
        ];
    }

    /** Recent registrations shown on the dashboard. */
    public static function recentUsers(): array
    {
        return [
            ['name' => 'Arun Prakash',   'email' => 'arun.prakash@gmail.com',    'registered' => '04 Sep 2026', 'last_active' => '2 hours ago', 'status' => 'Active'],
            ['name' => 'Divya Ramesh',   'email' => 'divya.ramesh@outlook.com',  'registered' => '03 Sep 2026', 'last_active' => '5 hours ago', 'status' => 'Active'],
            ['name' => 'Mohammed Irfan', 'email' => 'm.irfan@yahoo.in',          'registered' => '02 Sep 2026', 'last_active' => 'Yesterday',   'status' => 'Active'],
            ['name' => 'Sneha Kulkarni', 'email' => 'sneha.k@gmail.com',         'registered' => '01 Sep 2026', 'last_active' => '3 days ago',  'status' => 'Inactive'],
            ['name' => 'Pooja Agarwal',  'email' => 'pooja.agarwal@gmail.com',   'registered' => '27 Aug 2026', 'last_active' => 'Never',       'status' => 'Pending'],
        ];
    }

    /** Recent admin actions shown on the dashboard. */
    public static function recentAdminActivity(): array
    {
        return [
            ['admin' => 'Nandini Rao',         'action' => 'updated Gold Rate',       'detail' => 'Chennai 24K → ₹6,850',       'module' => 'Market Overview',    'time' => '09:42 AM',   'icon' => 'trending'],
            ['admin' => 'Admin',               'action' => 'added a new bank',        'detail' => 'AU Small Finance Bank',      'module' => 'Banking Utilities',  'time' => '09:15 AM',   'icon' => 'bank'],
            ['admin' => 'Priya Menon',         'action' => 'published an RBI update', 'detail' => 'Repo rate held at 6.50%',    'module' => 'Information',        'time' => 'Yesterday',  'icon' => 'info'],
            ['admin' => 'Nandini Rao',         'action' => 'updated loan comparison', 'detail' => 'SBI Home Loan → 8.40%',      'module' => 'Comparison',         'time' => 'Yesterday',  'icon' => 'sliders'],
            ['admin' => 'Karthik Subramanian', 'action' => 'added a scam alert',      'detail' => 'Digital-arrest video call',  'module' => 'Cyber Crime',        'time' => '2 days ago', 'icon' => 'shield'],
        ];
    }

    /** Dashboard quick-action buttons. */
    public static function quickActions(): array
    {
        return [
            ['label' => 'Add Gold Rate',   'icon' => 'trending',   'route' => 'backend.market.gold'],
            ['label' => 'Add Bank',        'icon' => 'bank',       'route' => 'backend.banking.directory'],
            ['label' => 'Add Holiday',     'icon' => 'calendar',   'route' => 'backend.banking.holidays'],
            ['label' => 'Add RBI Update',  'icon' => 'info',       'route' => 'backend.information.rbi'],
            ['label' => 'Add Scam Alert',  'icon' => 'shield',     'route' => 'backend.cyber-safety.scam-awareness'],
        ];
    }

    /** Unread items behind the header bell. */
    public static function headerNotifications(): array
    {
        return [
            ['title' => 'Fuel prices are a day behind', 'body' => 'Bengaluru petrol still shows the 21 Sep value.',  'time' => '18m ago',   'tone' => 'warning'],
            ['title' => 'Gold rates published',         'body' => '148 city-purity rows updated from IBJA.',         'time' => '1h ago',    'tone' => 'success'],
            ['title' => '3 new support requests',       'body' => 'One marked high priority by Vikram Shetty.',      'time' => '2h ago',    'tone' => 'primary'],
            ['title' => 'New user feedback',            'body' => 'Two ratings below 3 stars need a review.',        'time' => 'Yesterday', 'tone' => 'danger'],
        ];
    }

    /* ----------------------------------------------------- Calculators -- */

    /** Configuration + app-preview content for one financial tool. */
    public static function calculator(string $key): array
    {
        $all = [
            'emi' => ['name' => 'EMI Calculator', 'icon' => 'calculator', 'uses' => '12,480', 'desc' => 'Work out the monthly instalment for a loan from the amount, rate and tenure.',
                'fields' => [['Loan Amount', '₹1,00,000', '₹5,00,00,000', '₹25,00,000'], ['Interest Rate', '5%', '24%', '8.5%'], ['Tenure', '1 year', '30 years', '20 years']],
                'preview' => ['title' => 'Home Loan EMI', 'primary' => '₹21,696', 'primary_label' => 'Monthly EMI',
                    'rows' => [['Principal', '₹25,00,000'], ['Total Interest', '₹27,07,040'], ['Total Payable', '₹52,07,040']]]],

            // Reverse EMI is not in the current sidebar (see AdminMenu). Its
            // configuration is kept here so re-adding the tool is a one-line
            // menu entry rather than a rebuild.
            'reverse-emi' => ['name' => 'Reverse EMI', 'icon' => 'calculator', 'uses' => '2,180', 'desc' => 'Find the loan amount a borrower can take for an EMI they can afford.',
                'fields' => [['Affordable EMI', '₹1,000', '₹5,00,000', '₹25,000'], ['Interest Rate', '5%', '24%', '8.5%'], ['Tenure', '1 year', '30 years', '20 years']],
                'preview' => ['title' => 'Eligible Loan', 'primary' => '₹28,80,500', 'primary_label' => 'You can borrow',
                    'rows' => [['Chosen EMI', '₹25,000'], ['Tenure', '20 years'], ['Total Interest', '₹31,19,500']]]],

            'lumpsum' => ['name' => 'Lumpsum Calculator', 'icon' => 'chart', 'uses' => '4,310', 'desc' => 'Project the maturity value of a one-time investment.',
                'fields' => [['Investment', '₹1,000', '₹10,00,00,000', '₹5,00,000'], ['Expected Return', '1%', '30%', '12%'], ['Period', '1 year', '40 years', '10 years']],
                'preview' => ['title' => 'Lumpsum Growth', 'primary' => '₹15,52,924', 'primary_label' => 'Maturity value',
                    'rows' => [['Invested', '₹5,00,000'], ['Est. Returns', '₹10,52,924'], ['Period', '10 years']]]],

            'sip' => ['name' => 'SIP Calculator', 'icon' => 'chart', 'uses' => '9,240', 'desc' => 'Project the corpus built by a monthly systematic investment plan.',
                'fields' => [['Monthly Amount', '₹500', '₹10,00,000', '₹10,000'], ['Expected Return', '1%', '30%', '12%'], ['Period', '1 year', '40 years', '15 years']],
                'preview' => ['title' => 'SIP Projection', 'primary' => '₹50,45,760', 'primary_label' => 'Corpus at 15 years',
                    'rows' => [['Invested', '₹18,00,000'], ['Est. Returns', '₹32,45,760'], ['Monthly', '₹10,000']]]],

            'gst' => ['name' => 'GST Calculator', 'icon' => 'calculator', 'uses' => '2,940', 'desc' => 'Add or remove GST at the standard Indian slabs.',
                'fields' => [['Amount', '₹1', '₹1,00,00,000', '₹10,000'], ['GST Slab', '0%', '28%', '18%'], ['Mode', 'Exclusive', 'Inclusive', 'Exclusive']],
                'preview' => ['title' => 'GST Breakdown', 'primary' => '₹11,800', 'primary_label' => 'Total payable',
                    'rows' => [['Base Amount', '₹10,000'], ['CGST @ 9%', '₹900'], ['SGST @ 9%', '₹900']]]],

            'rd' => ['name' => 'RD Calculator', 'icon' => 'bank', 'uses' => '2,620', 'desc' => 'Maturity value of a recurring deposit at a chosen bank rate.',
                'fields' => [['Monthly Deposit', '₹100', '₹10,00,000', '₹5,000'], ['Interest Rate', '3%', '10%', '7%'], ['Tenure', '6 months', '10 years', '3 years']],
                'preview' => ['title' => 'RD Maturity', 'primary' => '₹2,01,420', 'primary_label' => 'On maturity',
                    'rows' => [['Total Deposited', '₹1,80,000'], ['Interest Earned', '₹21,420'], ['Tenure', '3 years']]]],

            'fd' => ['name' => 'FD Calculator', 'icon' => 'bank', 'uses' => '3,640', 'desc' => 'Maturity value of a fixed deposit with quarterly compounding.',
                'fields' => [['Principal', '₹1,000', '₹5,00,00,000', '₹2,00,000'], ['Interest Rate', '3%', '10%', '7.1%'], ['Tenure', '7 days', '10 years', '5 years']],
                'preview' => ['title' => 'FD Maturity', 'primary' => '₹2,84,318', 'primary_label' => 'On maturity',
                    'rows' => [['Principal', '₹2,00,000'], ['Interest Earned', '₹84,318'], ['Rate', '7.10% p.a.']]]],

            'inflation' => ['name' => 'Inflation Calculator', 'icon' => 'trending', 'uses' => '1,480', 'desc' => 'Show what a sum today will be worth after inflation.',
                'fields' => [['Current Amount', '₹1,000', '₹10,00,00,000', '₹10,00,000'], ['Inflation Rate', '1%', '15%', '6%'], ['Period', '1 year', '40 years', '10 years']],
                'preview' => ['title' => 'Future Value', 'primary' => '₹17,90,848', 'primary_label' => 'Needed in 10 years',
                    'rows' => [['Today', '₹10,00,000'], ['Inflation', '6% p.a.'], ['Purchasing power lost', '44.2%']]]],

            'retirement' => ['name' => 'Retirement Calculator', 'icon' => 'target', 'uses' => '2,410', 'desc' => 'Estimate the corpus needed to retire at a chosen age.',
                'fields' => [['Current Age', '18', '70', '30'], ['Retirement Age', '40', '75', '60'], ['Monthly Expense', '₹5,000', '₹10,00,000', '₹50,000']],
                'preview' => ['title' => 'Retirement Corpus', 'primary' => '₹4.82 Cr', 'primary_label' => 'Target at 60',
                    'rows' => [['Years to retire', '30'], ['Monthly SIP needed', '₹13,640'], ['Assumed return', '12% p.a.']]]],

            'tax' => ['name' => 'Tax Calculator', 'icon' => 'info', 'uses' => '3,120', 'desc' => 'Compare income tax under the old and new regimes.',
                'fields' => [['Annual Income', '₹1,00,000', '₹10,00,00,000', '₹12,00,000'], ['Regime', 'Old', 'New', 'New'], ['Deductions', '₹0', '₹5,00,000', '₹1,50,000']],
                'preview' => ['title' => 'Tax Payable', 'primary' => '₹71,500', 'primary_label' => 'New regime',
                    'rows' => [['Gross Income', '₹12,00,000'], ['Old regime tax', '₹1,01,400'], ['You save', '₹29,900']]]],

            'net-worth' => ['name' => 'Net Worth Calculator', 'icon' => 'chart', 'uses' => '1,820', 'desc' => 'Total assets minus liabilities, tracked over time.',
                'fields' => [['Total Assets', '₹0', '₹100 Cr', '₹85,00,000'], ['Total Liabilities', '₹0', '₹100 Cr', '₹32,00,000'], ['Review Frequency', 'Monthly', 'Yearly', 'Quarterly']],
                'preview' => ['title' => 'Net Worth', 'primary' => '₹53,00,000', 'primary_label' => 'Current position',
                    'rows' => [['Assets', '₹85,00,000'], ['Liabilities', '₹32,00,000'], ['Debt ratio', '37.6%']]]],

            'debt-to-income' => ['name' => 'Debt-to-Income Calculator', 'icon' => 'loan', 'uses' => '1,240', 'desc' => 'Ratio of monthly debt obligations to monthly income.',
                'fields' => [['Monthly Income', '₹10,000', '₹50,00,000', '₹1,20,000'], ['Monthly Debt', '₹0', '₹50,00,000', '₹38,000'], ['Warning Threshold', '20%', '60%', '40%']],
                'preview' => ['title' => 'DTI Ratio', 'primary' => '31.7%', 'primary_label' => 'Healthy range',
                    'rows' => [['Monthly Income', '₹1,20,000'], ['Monthly Debt', '₹38,000'], ['Lender comfort', 'Below 40%']]]],
        ];

        $tool = $all[$key] ?? reset($all);

        return $tool + ['status' => 'Active', 'updated' => '20 Sep 2026'];
    }

    /* ----------------------------------------------------------- Goals -- */

    /** Configuration + app-preview content for one goal template. */
    public static function goal(string $key): array
    {
        $all = [
            'home' => ['name' => 'Home Goal', 'icon' => 'bank', 'desc' => 'Save a down payment and plan the loan for a first home.',
                'duration' => '5 – 10 years', 'target' => '₹25,00,000', 'monthly' => '₹18,400', 'plans' => '2,140',
                'params' => [['Typical target', '₹25,00,000'], ['Down payment', '20% of property value'], ['Assumed return', '10% p.a.'], ['Suggested tenure', '7 years']],
                'tips' => ['Keep the down payment in debt funds, not equity, within three years of the purchase.', 'Budget 7–8% of property value for registration, stamp duty and interiors.', 'Check your CIBIL score a full year before applying.']],

            'marriage' => ['name' => 'Marriage Goal', 'icon' => 'target', 'desc' => 'Build a fund for wedding expenses over a fixed horizon.',
                'duration' => '3 – 7 years', 'target' => '₹15,00,000', 'monthly' => '₹16,200', 'plans' => '1,380',
                'params' => [['Typical target', '₹15,00,000'], ['Gold allocation', '15% of corpus'], ['Assumed return', '10% p.a.'], ['Suggested tenure', '5 years']],
                'tips' => ['Split the corpus between a short-duration debt fund and a gold ETF.', 'Revisit the target yearly — venue and catering inflate faster than CPI.', 'Avoid equity for any goal under three years away.']],

            'vehicle' => ['name' => 'Vehicle Goal', 'icon' => 'loan', 'desc' => 'Save a down payment for a car or two-wheeler and plan the loan around it.',
                'duration' => '2 – 5 years', 'target' => '₹9,00,000', 'monthly' => '₹13,900', 'plans' => '860',
                'params' => [['Typical target', '₹9,00,000'], ['Down payment', '20% of on-road price'], ['Assumed return', '9% p.a.'], ['Suggested tenure', '4 years']],
                'tips' => ['Budget the on-road price, not the ex-showroom price — insurance and registration add 8–10%.', 'A larger down payment cuts the loan tenure far more than it cuts the EMI.', 'Keep the fund in a short-duration debt fund; a vehicle goal is rarely more than five years away.']],

            'education' => ['name' => 'Education Goal', 'icon' => 'content', 'desc' => 'Plan for school, college or an overseas degree.',
                'duration' => '5 – 15 years', 'target' => '₹30,00,000', 'monthly' => '₹11,800', 'plans' => '1,920',
                'params' => [['Typical target', '₹30,00,000'], ['Education inflation', '9% p.a.'], ['Assumed return', '12% p.a.'], ['Suggested tenure', '12 years']],
                'tips' => ['Education inflation runs well above headline CPI — plan at 9%, not 6%.', 'Start a dedicated SIP the year the child is born for the best compounding.', 'Keep an education loan as a backstop, not the primary plan.']],

            'retirement' => ['name' => 'Retirement Goal', 'icon' => 'target', 'desc' => 'Accumulate a corpus that funds post-retirement expenses.',
                'duration' => '15 – 35 years', 'target' => '₹4,82,00,000', 'monthly' => '₹13,640', 'plans' => '1,020',
                'params' => [['Typical target', '₹4.82 Cr'], ['Retirement age', '60'], ['Assumed return', '12% p.a.'], ['Post-retirement inflation', '6% p.a.']],
                'tips' => ['Do not stop equity exposure at retirement — the corpus must last 25+ years.', 'Count EPF and NPS balances towards the same target.', 'Review the plan every three years, or after any salary jump.']],

            'custom' => ['name' => 'Custom Goals', 'icon' => 'plus', 'desc' => 'A blank template users configure for any personal target.',
                'duration' => 'User defined', 'target' => 'User defined', 'monthly' => 'Calculated', 'plans' => '380',
                'params' => [['Target amount', 'User defined'], ['Time horizon', 'User defined'], ['Assumed return', 'Default 10% p.a.'], ['Contribution', 'Monthly or lumpsum']],
                'tips' => ['Prompt users to name the goal — named goals are abandoned far less often.', 'Default the return assumption conservatively at 10%.', 'Offer a reminder cadence when the goal is created.']],
        ];

        $goal = $all[$key] ?? reset($all);

        return $goal + ['status' => 'Active', 'updated' => '19 Sep 2026'];
    }

    /* ----------------------------------------------- Roles & settings -- */

    /** The role matrix on Admin Management → Roles & Permissions. */
    public static function roles(): array
    {
        return [
            ['name' => 'Super Admin',   'description' => 'Full control, including admin accounts and permissions.',      'admins' => 1, 'granted' => 'all'],
            ['name' => 'Data Admin',    'description' => 'Maintains market rates, banking data and comparisons.',        'admins' => 1, 'granted' => ['market', 'banking', 'comparison']],
            ['name' => 'Content Admin', 'description' => 'Publishes RBI updates, safety content and help articles.',     'admins' => 2, 'granted' => ['information', 'cyber-safety']],
            ['name' => 'Product Admin', 'description' => 'Configures the calculators and goal templates in the app.',    'admins' => 1, 'granted' => ['tools', 'goals']],
            ['name' => 'Support Admin', 'description' => 'Handles user queries, feedback and support requests.',         'admins' => 1, 'granted' => ['users']],
        ];
    }

    /**
     * Toggles on Settings → Feature Controls.
     *
     * Grouped by `group` rather than by position, so the view can render the
     * cards without index arithmetic and adding a toggle never shifts a slice.
     */
    public static function featureToggles(): array
    {
        return [
            ['group' => 'Market Overview',    'label' => 'Gold Rates',         'desc' => 'City-wise 24K, 22K and 18K rates in the app.',     'on' => true],
            ['group' => 'Market Overview',    'label' => 'Silver Rates',       'desc' => 'Daily silver rates per kilogram.',                 'on' => true],
            ['group' => 'Market Overview',    'label' => 'Currency',           'desc' => 'Reference exchange rates against the rupee.',      'on' => true],
            ['group' => 'Market Overview',    'label' => 'Fuel Prices',        'desc' => 'Petrol, diesel and CNG prices by city.',           'on' => true],
            ['group' => 'Market Overview',    'label' => 'FD & RD Rates',      'desc' => 'Deposit rates published by each bank.',            'on' => true],

            ['group' => 'Tools & Planning',   'label' => 'Calculators',        'desc' => 'All eleven financial tools.',                      'on' => true],
            ['group' => 'Tools & Planning',   'label' => 'Goals',              'desc' => 'Goal templates and personal goal tracking.',       'on' => true],
            ['group' => 'Tools & Planning',   'label' => 'Comparison',         'desc' => 'Savings account and loan comparison journeys.',    'on' => true],

            ['group' => 'Banking & Safety',   'label' => 'Banking Utilities',  'desc' => 'IFSC, MICR, branch, ATM and holiday lookup.',      'on' => true],
            ['group' => 'Banking & Safety',   'label' => 'RBI Information',    'desc' => 'Policy updates and compliance content.',           'on' => true],
            ['group' => 'Banking & Safety',   'label' => 'Cyber Crime Help',   'desc' => 'Fraud reporting guidance and safety articles.',    'on' => true],
            ['group' => 'Banking & Safety',   'label' => 'Help Center',        'desc' => 'In-app help topics and support escalation.',       'on' => false],
        ];
    }

    /** The single user record behind User Management → User Details. */
    public static function userDetail(): array
    {
        return [
            'id' => 'BFZ-10241', 'name' => 'Arun Prakash', 'email' => 'arun.prakash@gmail.com',
            'mobile' => '+91 98407 22135', 'city' => 'Chennai, Tamil Nadu', 'status' => 'Active',
            'registered' => '04 Sep 2026', 'last_login' => 'Today, 09:42 AM', 'device' => 'Android 14 · Pixel 7a',
            'stats' => [
                ['label' => 'Calculations', 'value' => '142'],
                ['label' => 'Rate Views',   'value' => '318'],
                ['label' => 'Comparisons',  'value' => '12'],
                ['label' => 'Goal Plans',   'value' => '3'],
            ],
            'goals' => [
                ['name' => 'Home Goal',       'target' => '₹25,00,000', 'saved' => '₹6,40,000', 'progress' => 26],
                ['name' => 'Retirement Goal', 'target' => '₹4.82 Cr',   'saved' => '₹18,20,000', 'progress' => 4],
                ['name' => 'Education Goal',  'target' => '₹30,00,000', 'saved' => '₹2,10,000', 'progress' => 7],
            ],
            'timeline' => [
                ['action' => 'Calculated home loan EMI', 'detail' => '₹25,00,000 · 20 years · 8.5%', 'time' => 'Today, 09:42 AM'],
                ['action' => 'Viewed 24K gold rate',     'detail' => 'Chennai · ₹6,850 / 10g',       'time' => 'Today, 09:20 AM'],
                ['action' => 'Compared home loans',      'detail' => '4 lenders compared',           'time' => 'Yesterday, 08:14 PM'],
                ['action' => 'Updated Home Goal',        'detail' => 'Target raised to ₹25,00,000',  'time' => '20 Sep 2026'],
                ['action' => 'Registered on Bfinz',      'detail' => 'Android · Chennai',            'time' => '04 Sep 2026'],
            ],
        ];
    }
}
