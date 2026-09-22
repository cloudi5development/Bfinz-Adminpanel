<?php

namespace App\Support;

/**
 * Static table content for the admin panel's list screens.
 *
 * This is the seam where the panel will later meet the real backend: every list
 * page asks for its definition by route name and gets back columns, rows,
 * filters and an empty state. Swapping a case below for a repository/API call
 * changes nothing in the views.
 *
 * Column `type` drives how a cell renders (see components/admin/data-table):
 *   strong   — emphasised, used for the identifying column
 *   entity   — avatar chip + name, with "<key>_sub" as the second line
 *   mono     — tabular/monospace, for codes and identifiers
 *   amount   — right-aligned figures
 *   muted    — secondary information
 *   chip     — a neutral pill, for categories and types
 *   badge    — status pill, coloured by value
 *   rating   — five-star display
 *   delta    — signed change with an arrow
 *   text     — default
 */
class MockTables
{
    /** The table definition for a route, merged over sensible defaults. */
    public static function for(string $route): array
    {
        return array_merge([
            'columns' => [],
            'rows'    => [],
            'filters' => [],
            'primary' => null,
            'search'  => 'Search records',
            'export'  => true,
            'empty'   => ['title' => 'No records found', 'body' => 'Nothing matches the current filters yet.'],
            'total'   => null,
        ], static::definition($route) ?? []);
    }

    private static function definition(string $route): ?array
    {
        return match ($route) {

            /* ------------------------------------------ Market Overview -- */

            'backend.market.gold' => [
                'primary' => 'Add Gold Rate',
                'search'  => 'Search by city',
                'filters' => [
                    ['label' => 'Date', 'options' => ['Today', 'Yesterday', 'Last 7 days']],
                    ['label' => 'City', 'options' => ['Chennai', 'Mumbai', 'Delhi', 'Bengaluru', 'Hyderabad']],
                    ['label' => 'Purity', 'options' => ['24K', '22K', '18K']],
                    ['label' => 'Status', 'options' => ['Active', 'Inactive']],
                ],
                'columns' => [
                    ['key' => 'date', 'label' => 'Date'],
                    ['key' => 'city', 'label' => 'City', 'type' => 'strong'],
                    ['key' => 'purity', 'label' => 'Purity', 'type' => 'chip'],
                    ['key' => 'rate', 'label' => 'Rate', 'type' => 'amount'],
                    ['key' => 'unit', 'label' => 'Unit', 'type' => 'muted'],
                    ['key' => 'source', 'label' => 'Source'],
                    ['key' => 'updated', 'label' => 'Last Updated', 'type' => 'muted'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
                ],
                'rows' => [
                    ['date' => '22 Sep 2026', 'city' => 'Chennai', 'purity' => '24K', 'rate' => '₹6,850', 'unit' => '10 g', 'source' => 'IBJA', 'updated' => '09:30 AM', 'status' => 'Active'],
                    ['date' => '22 Sep 2026', 'city' => 'Chennai', 'purity' => '22K', 'rate' => '₹6,280', 'unit' => '10 g', 'source' => 'IBJA', 'updated' => '09:30 AM', 'status' => 'Active'],
                    ['date' => '22 Sep 2026', 'city' => 'Mumbai', 'purity' => '24K', 'rate' => '₹6,812', 'unit' => '10 g', 'source' => 'IBJA', 'updated' => '09:30 AM', 'status' => 'Active'],
                    ['date' => '22 Sep 2026', 'city' => 'Mumbai', 'purity' => '22K', 'rate' => '₹6,245', 'unit' => '10 g', 'source' => 'IBJA', 'updated' => '09:30 AM', 'status' => 'Active'],
                    ['date' => '22 Sep 2026', 'city' => 'Delhi', 'purity' => '24K', 'rate' => '₹6,868', 'unit' => '10 g', 'source' => 'IBJA', 'updated' => '09:28 AM', 'status' => 'Active'],
                    ['date' => '22 Sep 2026', 'city' => 'Bengaluru', 'purity' => '22K', 'rate' => '₹6,268', 'unit' => '10 g', 'source' => 'MMTC-PAMP', 'updated' => '09:28 AM', 'status' => 'Active'],
                    ['date' => '21 Sep 2026', 'city' => 'Hyderabad', 'purity' => '18K', 'rate' => '₹5,140', 'unit' => '10 g', 'source' => 'IBJA', 'updated' => 'Yesterday', 'status' => 'Inactive'],
                ],
            ],

            'backend.market.silver' => [
                'primary' => 'Add Silver Rate',
                'search'  => 'Search by city',
                'filters' => [
                    ['label' => 'Date', 'options' => ['Today', 'Yesterday', 'Last 7 days']],
                    ['label' => 'City', 'options' => ['Chennai', 'Mumbai', 'Delhi', 'Bengaluru']],
                    ['label' => 'Status', 'options' => ['Active', 'Inactive']],
                ],
                'columns' => [
                    ['key' => 'date', 'label' => 'Date'],
                    ['key' => 'city', 'label' => 'City', 'type' => 'strong'],
                    ['key' => 'rate', 'label' => 'Rate', 'type' => 'amount'],
                    ['key' => 'unit', 'label' => 'Unit', 'type' => 'muted'],
                    ['key' => 'source', 'label' => 'Source'],
                    ['key' => 'updated', 'label' => 'Last Updated', 'type' => 'muted'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
                ],
                'rows' => [
                    ['date' => '22 Sep 2026', 'city' => 'Chennai', 'rate' => '₹92,400', 'unit' => '1 kg', 'source' => 'IBJA', 'updated' => '09:30 AM', 'status' => 'Active'],
                    ['date' => '22 Sep 2026', 'city' => 'Mumbai', 'rate' => '₹91,950', 'unit' => '1 kg', 'source' => 'IBJA', 'updated' => '09:30 AM', 'status' => 'Active'],
                    ['date' => '22 Sep 2026', 'city' => 'Delhi', 'rate' => '₹92,180', 'unit' => '1 kg', 'source' => 'IBJA', 'updated' => '09:28 AM', 'status' => 'Active'],
                    ['date' => '22 Sep 2026', 'city' => 'Bengaluru', 'rate' => '₹92,050', 'unit' => '1 kg', 'source' => 'MMTC-PAMP', 'updated' => '09:28 AM', 'status' => 'Active'],
                    ['date' => '21 Sep 2026', 'city' => 'Kolkata', 'rate' => '₹91,700', 'unit' => '1 kg', 'source' => 'IBJA', 'updated' => 'Yesterday', 'status' => 'Inactive'],
                ],
            ],

            'backend.market.currency' => [
                'primary' => 'Add Currency Rate',
                'search'  => 'Search currency or code',
                'filters' => [
                    ['label' => 'Base Currency', 'options' => ['INR']],
                    ['label' => 'Status', 'options' => ['Active', 'Inactive']],
                ],
                'columns' => [
                    ['key' => 'pair', 'label' => 'Currency', 'type' => 'strong'],
                    ['key' => 'code', 'label' => 'Code', 'type' => 'mono'],
                    ['key' => 'buy', 'label' => 'Buy Rate', 'type' => 'amount'],
                    ['key' => 'sell', 'label' => 'Sell Rate', 'type' => 'amount'],
                    ['key' => 'base', 'label' => 'Base', 'type' => 'muted'],
                    ['key' => 'updated', 'label' => 'Last Updated', 'type' => 'muted'],
                    ['key' => 'source', 'label' => 'Source'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
                ],
                'rows' => [
                    ['pair' => 'US Dollar / Rupee', 'code' => 'USD/INR', 'buy' => '₹83.21', 'sell' => '₹83.64', 'base' => 'INR', 'updated' => '09:45 AM', 'source' => 'RBI Reference', 'status' => 'Active'],
                    ['pair' => 'Euro / Rupee', 'code' => 'EUR/INR', 'buy' => '₹90.18', 'sell' => '₹90.72', 'base' => 'INR', 'updated' => '09:45 AM', 'source' => 'RBI Reference', 'status' => 'Active'],
                    ['pair' => 'Pound Sterling / Rupee', 'code' => 'GBP/INR', 'buy' => '₹105.42', 'sell' => '₹106.08', 'base' => 'INR', 'updated' => '09:45 AM', 'source' => 'RBI Reference', 'status' => 'Active'],
                    ['pair' => 'UAE Dirham / Rupee', 'code' => 'AED/INR', 'buy' => '₹22.65', 'sell' => '₹22.83', 'base' => 'INR', 'updated' => '09:45 AM', 'source' => 'RBI Reference', 'status' => 'Active'],
                    ['pair' => 'Singapore Dollar / Rupee', 'code' => 'SGD/INR', 'buy' => '₹61.84', 'sell' => '₹62.20', 'base' => 'INR', 'updated' => '09:40 AM', 'source' => 'RBI Reference', 'status' => 'Active'],
                    ['pair' => 'Japanese Yen / Rupee', 'code' => 'JPY/INR', 'buy' => '₹0.5612', 'sell' => '₹0.5668', 'base' => 'INR', 'updated' => 'Yesterday', 'source' => 'RBI Reference', 'status' => 'Inactive'],
                ],
            ],

            'backend.market.fuel' => [
                'primary' => 'Add Fuel Price',
                'search'  => 'Search city or state',
                'filters' => [
                    ['label' => 'State', 'options' => ['Tamil Nadu', 'Maharashtra', 'Delhi', 'Karnataka']],
                    ['label' => 'Date', 'options' => ['Today', 'Yesterday']],
                ],
                'columns' => [
                    ['key' => 'state', 'label' => 'State'],
                    ['key' => 'city', 'label' => 'City', 'type' => 'strong'],
                    ['key' => 'petrol', 'label' => 'Petrol', 'type' => 'amount'],
                    ['key' => 'diesel', 'label' => 'Diesel', 'type' => 'amount'],
                    ['key' => 'cng', 'label' => 'CNG', 'type' => 'amount'],
                    ['key' => 'updated', 'label' => 'Updated Date', 'type' => 'muted'],
                    ['key' => 'source', 'label' => 'Source'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
                ],
                'rows' => [
                    ['state' => 'Tamil Nadu', 'city' => 'Chennai', 'petrol' => '₹102.63', 'diesel' => '₹94.24', 'cng' => '₹86.50', 'updated' => '22 Sep 2026', 'source' => 'IOCL', 'status' => 'Active'],
                    ['state' => 'Tamil Nadu', 'city' => 'Coimbatore', 'petrol' => '₹102.18', 'diesel' => '₹93.85', 'cng' => '₹86.10', 'updated' => '22 Sep 2026', 'source' => 'IOCL', 'status' => 'Active'],
                    ['state' => 'Maharashtra', 'city' => 'Mumbai', 'petrol' => '₹106.31', 'diesel' => '₹94.27', 'cng' => '₹79.00', 'updated' => '22 Sep 2026', 'source' => 'HPCL', 'status' => 'Active'],
                    ['state' => 'Delhi', 'city' => 'New Delhi', 'petrol' => '₹94.72', 'diesel' => '₹87.62', 'cng' => '₹75.09', 'updated' => '22 Sep 2026', 'source' => 'IOCL', 'status' => 'Active'],
                    ['state' => 'Karnataka', 'city' => 'Bengaluru', 'petrol' => '₹102.86', 'diesel' => '₹88.94', 'cng' => '₹84.50', 'updated' => '21 Sep 2026', 'source' => 'BPCL', 'status' => 'Warning'],
                ],
            ],

            'backend.market.fd' => [
                'primary' => 'Add FD Rate',
                'search'  => 'Search bank',
                'filters' => [
                    ['label' => 'Bank', 'options' => ['SBI', 'HDFC Bank', 'ICICI Bank', 'Axis Bank']],
                    ['label' => 'Tenure', 'options' => ['< 1 year', '1–3 years', '3–5 years', '> 5 years']],
                    ['label' => 'Status', 'options' => ['Active', 'Inactive']],
                ],
                'columns' => [
                    ['key' => 'bank', 'label' => 'Bank', 'type' => 'entity'],
                    ['key' => 'tenure', 'label' => 'Tenure'],
                    ['key' => 'rate', 'label' => 'Interest Rate', 'type' => 'amount'],
                    ['key' => 'senior', 'label' => 'Senior Citizen', 'type' => 'amount'],
                    ['key' => 'effective', 'label' => 'Effective Date', 'type' => 'muted'],
                    ['key' => 'source', 'label' => 'Source'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
                ],
                'rows' => [
                    ['bank' => 'State Bank of India', 'bank_sub' => 'Public Sector', 'tenure' => '1 year – 2 years', 'rate' => '6.80%', 'senior' => '7.30%', 'effective' => '15 Sep 2026', 'source' => 'Bank Website', 'status' => 'Active'],
                    ['bank' => 'HDFC Bank', 'bank_sub' => 'Private Sector', 'tenure' => '1 year – 2 years', 'rate' => '7.10%', 'senior' => '7.60%', 'effective' => '12 Sep 2026', 'source' => 'Bank Website', 'status' => 'Active'],
                    ['bank' => 'ICICI Bank', 'bank_sub' => 'Private Sector', 'tenure' => '2 years – 3 years', 'rate' => '7.00%', 'senior' => '7.50%', 'effective' => '12 Sep 2026', 'source' => 'Bank Website', 'status' => 'Active'],
                    ['bank' => 'Axis Bank', 'bank_sub' => 'Private Sector', 'tenure' => '3 years – 5 years', 'rate' => '7.10%', 'senior' => '7.85%', 'effective' => '08 Sep 2026', 'source' => 'Bank Website', 'status' => 'Active'],
                    ['bank' => 'Bank of Baroda', 'bank_sub' => 'Public Sector', 'tenure' => '5 years +', 'rate' => '6.50%', 'senior' => '7.15%', 'effective' => '01 Sep 2026', 'source' => 'Bank Website', 'status' => 'Inactive'],
                ],
            ],

            'backend.market.rd' => [
                'primary' => 'Add RD Rate',
                'search'  => 'Search bank',
                'filters' => [
                    ['label' => 'Bank', 'options' => ['SBI', 'HDFC Bank', 'ICICI Bank', 'Canara Bank']],
                    ['label' => 'Tenure', 'options' => ['1 year', '2 years', '3 years', '5 years']],
                    ['label' => 'Status', 'options' => ['Active', 'Inactive']],
                ],
                'columns' => [
                    ['key' => 'bank', 'label' => 'Bank', 'type' => 'entity'],
                    ['key' => 'tenure', 'label' => 'Tenure'],
                    ['key' => 'rate', 'label' => 'Interest Rate', 'type' => 'amount'],
                    ['key' => 'effective', 'label' => 'Effective Date', 'type' => 'muted'],
                    ['key' => 'source', 'label' => 'Source'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
                ],
                'rows' => [
                    ['bank' => 'State Bank of India', 'bank_sub' => 'Public Sector', 'tenure' => '1 year', 'rate' => '6.50%', 'effective' => '15 Sep 2026', 'source' => 'Bank Website', 'status' => 'Active'],
                    ['bank' => 'HDFC Bank', 'bank_sub' => 'Private Sector', 'tenure' => '2 years', 'rate' => '7.00%', 'effective' => '12 Sep 2026', 'source' => 'Bank Website', 'status' => 'Active'],
                    ['bank' => 'ICICI Bank', 'bank_sub' => 'Private Sector', 'tenure' => '3 years', 'rate' => '7.10%', 'effective' => '12 Sep 2026', 'source' => 'Bank Website', 'status' => 'Active'],
                    ['bank' => 'Canara Bank', 'bank_sub' => 'Public Sector', 'tenure' => '5 years', 'rate' => '6.85%', 'effective' => '05 Sep 2026', 'source' => 'Bank Website', 'status' => 'Active'],
                ],
            ],

            /* ----------------------------------------------- Comparison -- */

            'backend.comparison.savings' => [
                'primary' => 'Add Savings Product',
                'search'  => 'Search bank or account type',
                'filters' => [
                    ['label' => 'Bank', 'options' => ['SBI', 'HDFC Bank', 'ICICI Bank', 'Axis Bank', 'Kotak Mahindra Bank']],
                    ['label' => 'Minimum Balance', 'options' => ['Zero balance', 'Up to ₹10,000', 'Above ₹10,000']],
                    ['label' => 'Status', 'options' => ['Active', 'Inactive']],
                ],
                'columns' => [
                    ['key' => 'bank', 'label' => 'Bank', 'type' => 'entity'],
                    ['key' => 'type', 'label' => 'Account Type', 'type' => 'strong'],
                    ['key' => 'min_balance', 'label' => 'Minimum Balance', 'type' => 'amount'],
                    ['key' => 'rate', 'label' => 'Interest Rate', 'type' => 'amount'],
                    ['key' => 'charges', 'label' => 'Non-maintenance Charge', 'type' => 'amount'],
                    ['key' => 'features', 'label' => 'Key Features', 'type' => 'muted'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
                ],
                'rows' => [
                    ['bank' => 'State Bank of India', 'bank_sub' => 'Public Sector', 'type' => 'Basic Savings (BSBD)', 'min_balance' => '₹0', 'rate' => '2.70%', 'charges' => '₹0', 'features' => 'Zero balance, RuPay debit card', 'status' => 'Active'],
                    ['bank' => 'ICICI Bank', 'bank_sub' => 'Private Sector', 'type' => 'Salary Account', 'min_balance' => '₹0', 'rate' => '3.00%', 'charges' => '₹0', 'features' => 'Zero balance, insurance cover', 'status' => 'Active'],
                    ['bank' => 'HDFC Bank', 'bank_sub' => 'Private Sector', 'type' => 'Regular Savings', 'min_balance' => '₹10,000', 'rate' => '3.00%', 'charges' => '₹600', 'features' => 'Free NEFT, cheque book', 'status' => 'Active'],
                    ['bank' => 'Kotak Mahindra Bank', 'bank_sub' => 'Private Sector', 'type' => 'Edge Savings', 'min_balance' => '₹10,000', 'rate' => '3.50%', 'charges' => '₹500', 'features' => 'Free DD, unlimited ATM use', 'status' => 'Active'],
                    ['bank' => 'Axis Bank', 'bank_sub' => 'Private Sector', 'type' => 'Prime Savings', 'min_balance' => '₹25,000', 'rate' => '3.50%', 'charges' => '₹750', 'features' => 'Priority service, lounge access', 'status' => 'Active'],
                ],
            ],

            'backend.comparison.loans' => [
                'primary' => 'Add Comparison Entry',
                'search'  => 'Search provider or loan type',
                'filters' => [
                    ['label' => 'Loan Type', 'options' => ['Home Loan', 'Personal Loan', 'Vehicle Loan', 'Education Loan', 'Gold Loan']],
                    ['label' => 'Provider Type', 'options' => ['Bank', 'NBFC', 'Housing Finance']],
                    ['label' => 'Status', 'options' => ['Active', 'Inactive']],
                ],
                'columns' => [
                    ['key' => 'provider', 'label' => 'Provider', 'type' => 'entity'],
                    ['key' => 'type', 'label' => 'Loan Type', 'type' => 'chip'],
                    ['key' => 'rate', 'label' => 'Interest Rate', 'type' => 'amount'],
                    ['key' => 'tenure', 'label' => 'Tenure'],
                    ['key' => 'fee', 'label' => 'Processing Fee', 'type' => 'amount'],
                    ['key' => 'emi', 'label' => 'EMI (₹25L / 20y)', 'type' => 'amount'],
                    ['key' => 'eligibility', 'label' => 'Eligibility', 'type' => 'muted'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
                ],
                'rows' => [
                    ['provider' => 'State Bank of India', 'provider_sub' => 'Bank', 'type' => 'Home Loan', 'rate' => '8.40%', 'tenure' => '20 years', 'fee' => '0.35%', 'emi' => '₹21,558', 'eligibility' => 'CIBIL 750+', 'status' => 'Active'],
                    ['provider' => 'HDFC Bank', 'provider_sub' => 'Bank', 'type' => 'Home Loan', 'rate' => '8.50%', 'tenure' => '20 years', 'fee' => '0.50%', 'emi' => '₹21,696', 'eligibility' => 'CIBIL 750+', 'status' => 'Active'],
                    ['provider' => 'LIC Housing Finance', 'provider_sub' => 'Housing Finance', 'type' => 'Home Loan', 'rate' => '8.65%', 'tenure' => '20 years', 'fee' => '0.25%', 'emi' => '₹21,904', 'eligibility' => 'CIBIL 700+', 'status' => 'Active'],
                    ['provider' => 'ICICI Bank', 'provider_sub' => 'Bank', 'type' => 'Home Loan', 'rate' => '8.75%', 'tenure' => '20 years', 'fee' => '0.50%', 'emi' => '₹22,043', 'eligibility' => 'CIBIL 750+', 'status' => 'Active'],
                    ['provider' => 'Bajaj Finserv', 'provider_sub' => 'NBFC', 'type' => 'Personal Loan', 'rate' => '11.00%', 'tenure' => '5 years', 'fee' => '3.93%', 'emi' => '₹54,348', 'eligibility' => 'CIBIL 720+', 'status' => 'Active'],
                    ['provider' => 'Muthoot Finance', 'provider_sub' => 'NBFC', 'type' => 'Gold Loan', 'rate' => '9.90%', 'tenure' => '3 years', 'fee' => '1.00%', 'emi' => '₹80,585', 'eligibility' => 'Gold collateral', 'status' => 'Inactive'],
                ],
            ],

            /* ---------------------------------------- Banking Utilities -- */

            'backend.banking.holidays' => [
                'primary' => 'Add Holiday',
                'search'  => 'Search holiday or state',
                'filters' => [
                    ['label' => 'Month', 'options' => ['September', 'October', 'November', 'December']],
                    ['label' => 'State', 'options' => ['All India', 'Tamil Nadu', 'Maharashtra', 'Karnataka', 'Delhi']],
                    ['label' => 'Type', 'options' => ['RBI Holiday', 'Negotiable Instruments Act', 'Regional']],
                ],
                'columns' => [
                    ['key' => 'date', 'label' => 'Date', 'type' => 'strong'],
                    ['key' => 'day', 'label' => 'Day', 'type' => 'muted'],
                    ['key' => 'holiday', 'label' => 'Holiday'],
                    ['key' => 'type', 'label' => 'Type', 'type' => 'chip'],
                    ['key' => 'states', 'label' => 'Applicable States'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
                ],
                'rows' => [
                    ['date' => '02 Oct 2026', 'day' => 'Friday', 'holiday' => 'Gandhi Jayanti', 'type' => 'RBI Holiday', 'states' => 'All India', 'status' => 'Active'],
                    ['date' => '20 Oct 2026', 'day' => 'Tuesday', 'holiday' => 'Deepavali (Naraka Chaturdashi)', 'type' => 'Negotiable Instruments Act', 'states' => 'Tamil Nadu, Karnataka', 'status' => 'Active'],
                    ['date' => '21 Oct 2026', 'day' => 'Wednesday', 'holiday' => 'Diwali Balipratipada', 'type' => 'Negotiable Instruments Act', 'states' => 'Maharashtra, Gujarat', 'status' => 'Active'],
                    ['date' => '05 Nov 2026', 'day' => 'Thursday', 'holiday' => 'Guru Nanak Jayanti', 'type' => 'RBI Holiday', 'states' => 'All India', 'status' => 'Active'],
                    ['date' => '25 Dec 2026', 'day' => 'Friday', 'holiday' => 'Christmas', 'type' => 'RBI Holiday', 'states' => 'All India', 'status' => 'Active'],
                    ['date' => '01 Nov 2026', 'day' => 'Sunday', 'holiday' => 'Kannada Rajyotsava', 'type' => 'Regional', 'states' => 'Karnataka', 'status' => 'Inactive'],
                ],
            ],

            'backend.banking.atms' => [
                'primary' => 'Add ATM',
                'search'  => 'Search ATM location',
                'filters' => [
                    ['label' => 'Bank', 'options' => ['SBI', 'HDFC Bank', 'ICICI Bank']],
                    ['label' => 'City', 'options' => ['Chennai', 'Mumbai', 'Bengaluru']],
                    ['label' => 'Status', 'options' => ['Active', 'Offline']],
                ],
                'columns' => [
                    ['key' => 'bank', 'label' => 'Bank', 'type' => 'strong'],
                    ['key' => 'address', 'label' => 'ATM Address'],
                    ['key' => 'city', 'label' => 'City'],
                    ['key' => 'state', 'label' => 'State'],
                    ['key' => 'geo', 'label' => 'Lat / Long', 'type' => 'mono'],
                    ['key' => 'services', 'label' => 'Services', 'type' => 'muted'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
                ],
                'rows' => [
                    ['bank' => 'State Bank of India', 'address' => 'Anna Salai, Thousand Lights', 'city' => 'Chennai', 'state' => 'Tamil Nadu', 'geo' => '13.0604, 80.2496', 'services' => 'Cash, Deposit, 24×7', 'status' => 'Active'],
                    ['bank' => 'HDFC Bank', 'address' => 'Usman Road, T. Nagar', 'city' => 'Chennai', 'state' => 'Tamil Nadu', 'geo' => '13.0418, 80.2341', 'services' => 'Cash, 24×7', 'status' => 'Active'],
                    ['bank' => 'ICICI Bank', 'address' => 'Andheri Kurla Road', 'city' => 'Mumbai', 'state' => 'Maharashtra', 'geo' => '19.1136, 72.8697', 'services' => 'Cash, Deposit', 'status' => 'Active'],
                    ['bank' => 'Axis Bank', 'address' => '80 Feet Road, Koramangala', 'city' => 'Bengaluru', 'state' => 'Karnataka', 'geo' => '12.9352, 77.6245', 'services' => 'Cash only', 'status' => 'Offline'],
                ],
            ],

            'backend.banking.ifsc' => [
                'primary' => 'Add IFSC & MICR Record',
                'search'  => 'Search IFSC code, MICR code, bank or branch',
                'total'   => 168420,
                'filters' => [
                    ['label' => 'Bank', 'options' => ['SBI', 'HDFC Bank', 'ICICI Bank', 'Axis Bank']],
                    ['label' => 'State', 'options' => ['Tamil Nadu', 'Maharashtra', 'Karnataka', 'Delhi']],
                    ['label' => 'Status', 'options' => ['Active', 'Inactive']],
                ],
                'columns' => [
                    ['key' => 'ifsc', 'label' => 'IFSC', 'type' => 'mono'],
                    ['key' => 'micr', 'label' => 'MICR', 'type' => 'mono'],
                    ['key' => 'bank', 'label' => 'Bank', 'type' => 'strong'],
                    ['key' => 'branch', 'label' => 'Branch'],
                    ['key' => 'city', 'label' => 'City'],
                    ['key' => 'district', 'label' => 'District', 'type' => 'muted'],
                    ['key' => 'state', 'label' => 'State'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
                ],
                'rows' => [
                    ['ifsc' => 'SBIN0000800', 'micr' => '600002004', 'bank' => 'State Bank of India', 'branch' => 'Anna Salai', 'city' => 'Chennai', 'district' => 'Chennai', 'state' => 'Tamil Nadu', 'status' => 'Active'],
                    ['ifsc' => 'HDFC0000521', 'micr' => '600240009', 'bank' => 'HDFC Bank', 'branch' => 'T. Nagar', 'city' => 'Chennai', 'district' => 'Chennai', 'state' => 'Tamil Nadu', 'status' => 'Active'],
                    ['ifsc' => 'ICIC0001203', 'micr' => '400229022', 'bank' => 'ICICI Bank', 'branch' => 'Andheri East', 'city' => 'Mumbai', 'district' => 'Mumbai Suburban', 'state' => 'Maharashtra', 'status' => 'Active'],
                    ['ifsc' => 'UTIB0000234', 'micr' => '560211011', 'bank' => 'Axis Bank', 'branch' => 'Koramangala', 'city' => 'Bengaluru', 'district' => 'Bengaluru Urban', 'state' => 'Karnataka', 'status' => 'Active'],
                    ['ifsc' => 'BARB0VJKORA', 'micr' => '110012009', 'bank' => 'Bank of Baroda', 'branch' => 'Connaught Place', 'city' => 'New Delhi', 'district' => 'Central Delhi', 'state' => 'Delhi', 'status' => 'Active'],
                    ['ifsc' => 'KKBK0000432', 'micr' => '500485006', 'bank' => 'Kotak Mahindra Bank', 'branch' => 'Banjara Hills', 'city' => 'Hyderabad', 'district' => 'Hyderabad', 'state' => 'Telangana', 'status' => 'Inactive'],
                ],
            ],

            'backend.banking.branches' => [
                'primary' => 'Add Branch',
                'search'  => 'Search branch or city',
                'filters' => [
                    ['label' => 'Bank', 'options' => ['SBI', 'HDFC Bank', 'ICICI Bank']],
                    ['label' => 'State', 'options' => ['Tamil Nadu', 'Maharashtra', 'Karnataka']],
                    ['label' => 'Status', 'options' => ['Active', 'Inactive']],
                ],
                'columns' => [
                    ['key' => 'branch', 'label' => 'Branch Name', 'type' => 'strong'],
                    ['key' => 'bank', 'label' => 'Bank'],
                    ['key' => 'address', 'label' => 'Address', 'type' => 'muted'],
                    ['key' => 'city', 'label' => 'City'],
                    ['key' => 'state', 'label' => 'State'],
                    ['key' => 'geo', 'label' => 'Lat / Long', 'type' => 'mono'],
                    ['key' => 'phone', 'label' => 'Phone', 'type' => 'mono'],
                    ['key' => 'hours', 'label' => 'Working Hours', 'type' => 'muted'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
                ],
                'rows' => [
                    ['branch' => 'Anna Salai', 'bank' => 'State Bank of India', 'address' => '155 Anna Salai, Thousand Lights', 'city' => 'Chennai', 'state' => 'Tamil Nadu', 'geo' => '13.0604, 80.2496', 'phone' => '044 2852 3421', 'hours' => 'Mon–Fri 10:00–16:00', 'status' => 'Active'],
                    ['branch' => 'T. Nagar', 'bank' => 'HDFC Bank', 'address' => '44 Usman Road, T. Nagar', 'city' => 'Chennai', 'state' => 'Tamil Nadu', 'geo' => '13.0418, 80.2341', 'phone' => '044 2815 9080', 'hours' => 'Mon–Sat 09:30–16:30', 'status' => 'Active'],
                    ['branch' => 'Andheri East', 'bank' => 'ICICI Bank', 'address' => 'Chakala, Andheri Kurla Road', 'city' => 'Mumbai', 'state' => 'Maharashtra', 'geo' => '19.1136, 72.8697', 'phone' => '022 2832 4455', 'hours' => 'Mon–Fri 10:00–16:00', 'status' => 'Active'],
                    ['branch' => 'Koramangala', 'bank' => 'Axis Bank', 'address' => '80 Feet Road, 4th Block', 'city' => 'Bengaluru', 'state' => 'Karnataka', 'geo' => '12.9352, 77.6245', 'phone' => '080 4112 7788', 'hours' => 'Mon–Sat 10:00–16:00', 'status' => 'Inactive'],
                ],
            ],

            'backend.banking.forms' => [
                'primary' => 'Add Banking Form',
                'search'  => 'Search form name',
                'filters' => [
                    ['label' => 'Bank', 'options' => ['SBI', 'HDFC Bank', 'ICICI Bank']],
                    ['label' => 'Category', 'options' => ['Account Opening', 'KYC', 'Loans', 'Cards']],
                    ['label' => 'Status', 'options' => ['Published', 'Draft']],
                ],
                'columns' => [
                    ['key' => 'form', 'label' => 'Form Name', 'type' => 'strong'],
                    ['key' => 'bank', 'label' => 'Bank'],
                    ['key' => 'category', 'label' => 'Category', 'type' => 'chip'],
                    ['key' => 'file', 'label' => 'File Type', 'type' => 'mono'],
                    ['key' => 'uploaded', 'label' => 'Uploaded Date', 'type' => 'muted'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
                ],
                'rows' => [
                    ['form' => 'Savings Account Opening Form', 'bank' => 'State Bank of India', 'category' => 'Account Opening', 'file' => 'PDF · 420 KB', 'uploaded' => '18 Sep 2026', 'status' => 'Published'],
                    ['form' => 'KYC Update Form', 'bank' => 'HDFC Bank', 'category' => 'KYC', 'file' => 'PDF · 310 KB', 'uploaded' => '16 Sep 2026', 'status' => 'Published'],
                    ['form' => 'Home Loan Application', 'bank' => 'ICICI Bank', 'category' => 'Loans', 'file' => 'PDF · 780 KB', 'uploaded' => '12 Sep 2026', 'status' => 'Published'],
                    ['form' => 'Credit Card Closure Request', 'bank' => 'Axis Bank', 'category' => 'Cards', 'file' => 'PDF · 190 KB', 'uploaded' => '09 Sep 2026', 'status' => 'Draft'],
                ],
            ],

            'backend.banking.cheque' => [
                'primary' => 'Add Cheque Guide',
                'search'  => 'Search cheque guidance',
                'filters' => [
                    ['label' => 'Category', 'options' => ['Writing a Cheque', 'Clearing', 'Dishonour', 'Stop Payment', 'CTS Rules']],
                    ['label' => 'Status', 'options' => ['Published', 'Draft']],
                ],
                'columns' => [
                    ['key' => 'title', 'label' => 'Title', 'type' => 'strong'],
                    ['key' => 'category', 'label' => 'Category', 'type' => 'chip'],
                    ['key' => 'description', 'label' => 'Description', 'type' => 'muted'],
                    ['key' => 'source', 'label' => 'Reference'],
                    ['key' => 'published', 'label' => 'Published Date'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
                ],
                'rows' => [
                    ['title' => 'How to write a cheque correctly', 'category' => 'Writing a Cheque', 'description' => 'Date, payee, amount in words and figures, and the signature rules.', 'source' => 'RBI', 'published' => '18 Sep 2026', 'status' => 'Published'],
                    ['title' => 'Cheque bounce: charges and remedies', 'category' => 'Dishonour', 'description' => 'What happens after a dishonoured cheque, and Section 138 timelines.', 'source' => 'NI Act 1881', 'published' => '16 Sep 2026', 'status' => 'Published'],
                    ['title' => 'CTS-2010 cheque standards explained', 'category' => 'CTS Rules', 'description' => 'Why non-CTS cheques are rejected and how to replace a cheque book.', 'source' => 'RBI', 'published' => '14 Sep 2026', 'status' => 'Published'],
                    ['title' => 'Cheque clearing timelines', 'category' => 'Clearing', 'description' => 'Local and outstation clearing cycles under CTS grid clearing.', 'source' => 'RBI', 'published' => '10 Sep 2026', 'status' => 'Published'],
                    ['title' => 'How to stop payment on a cheque', 'category' => 'Stop Payment', 'description' => 'Branch and net-banking process, plus the applicable charges.', 'source' => 'Bank schedules', 'published' => '—', 'status' => 'Draft'],
                ],
            ],

            'backend.banking.directory' => [
                'primary' => 'Add Bank',
                'search'  => 'Search bank name',
                'filters' => [
                    ['label' => 'Bank Type', 'options' => ['Public Sector', 'Private Sector', 'Small Finance', 'Payments Bank']],
                    ['label' => 'Status', 'options' => ['Active', 'Inactive']],
                ],
                // Carries the bank profile fields too (headquarters, year
                // established), so the directory is the one place a bank is
                // described rather than two half-screens.
                'columns' => [
                    ['key' => 'bank', 'label' => 'Bank', 'type' => 'entity'],
                    ['key' => 'type', 'label' => 'Bank Type', 'type' => 'chip'],
                    ['key' => 'hq', 'label' => 'Headquarters'],
                    ['key' => 'established', 'label' => 'Established', 'type' => 'muted'],
                    ['key' => 'website', 'label' => 'Website', 'type' => 'muted'],
                    ['key' => 'care', 'label' => 'Customer Care', 'type' => 'mono'],
                    ['key' => 'branches', 'label' => 'Branches', 'type' => 'amount'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
                ],
                'rows' => [
                    ['bank' => 'State Bank of India', 'bank_sub' => 'SBIN', 'type' => 'Public Sector', 'hq' => 'Mumbai, Maharashtra', 'established' => '1955', 'website' => 'sbi.co.in', 'care' => '1800 1234', 'branches' => '22,405', 'status' => 'Active'],
                    ['bank' => 'HDFC Bank', 'bank_sub' => 'HDFC', 'type' => 'Private Sector', 'hq' => 'Mumbai, Maharashtra', 'established' => '1994', 'website' => 'hdfcbank.com', 'care' => '1800 1600', 'branches' => '8,851', 'status' => 'Active'],
                    ['bank' => 'ICICI Bank', 'bank_sub' => 'ICIC', 'type' => 'Private Sector', 'hq' => 'Vadodara, Gujarat', 'established' => '1994', 'website' => 'icicibank.com', 'care' => '1860 120 7777', 'branches' => '6,523', 'status' => 'Active'],
                    ['bank' => 'Axis Bank', 'bank_sub' => 'UTIB', 'type' => 'Private Sector', 'hq' => 'Mumbai, Maharashtra', 'established' => '1993', 'website' => 'axisbank.com', 'care' => '1860 419 5555', 'branches' => '5,377', 'status' => 'Active'],
                    ['bank' => 'Bank of Baroda', 'bank_sub' => 'BARB', 'type' => 'Public Sector', 'hq' => 'Vadodara, Gujarat', 'established' => '1908', 'website' => 'bankofbaroda.in', 'care' => '1800 5700', 'branches' => '8,168', 'status' => 'Active'],
                    ['bank' => 'Kotak Mahindra Bank', 'bank_sub' => 'KKBK', 'type' => 'Private Sector', 'hq' => 'Mumbai, Maharashtra', 'established' => '2003', 'website' => 'kotak.com', 'care' => '1860 266 2666', 'branches' => '1,996', 'status' => 'Active'],
                    ['bank' => 'AU Small Finance Bank', 'bank_sub' => 'AUBL', 'type' => 'Small Finance', 'hq' => 'Jaipur, Rajasthan', 'established' => '2017', 'website' => 'aubank.in', 'care' => '1800 1200 1200', 'branches' => '2,383', 'status' => 'Inactive'],
                ],
            ],

            /* ---------------------------------------------- Information -- */

            'backend.information.rbi' => [
                'primary' => 'Add RBI Update',
                'search'  => 'Search RBI updates',
                'filters' => [
                    ['label' => 'Category', 'options' => ['Monetary Policy', 'Circulars', 'Master Directions']],
                    ['label' => 'Status', 'options' => ['Published', 'Draft']],
                ],
                'columns' => [
                    ['key' => 'title', 'label' => 'Title', 'type' => 'strong'],
                    ['key' => 'category', 'label' => 'Category', 'type' => 'chip'],
                    ['key' => 'description', 'label' => 'Description', 'type' => 'muted'],
                    ['key' => 'source', 'label' => 'Source'],
                    ['key' => 'published', 'label' => 'Published Date'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
                ],
                'rows' => [
                    ['title' => 'Repo rate held at 6.50%', 'category' => 'Monetary Policy', 'description' => 'MPC keeps the policy repo rate unchanged for the tenth review.', 'source' => 'rbi.org.in', 'published' => '20 Sep 2026', 'status' => 'Published'],
                    ['title' => 'Revised KYC master direction', 'category' => 'Master Directions', 'description' => 'Periodic updation timelines revised for low-risk customers.', 'source' => 'rbi.org.in', 'published' => '16 Sep 2026', 'status' => 'Published'],
                    ['title' => 'Digital lending guidelines — FAQ', 'category' => 'Circulars', 'description' => 'Clarifications on first-loss default guarantee arrangements.', 'source' => 'rbi.org.in', 'published' => '11 Sep 2026', 'status' => 'Published'],
                    ['title' => 'Draft framework on card tokenisation', 'category' => 'Circulars', 'description' => 'Consultation open for comments until October.', 'source' => 'rbi.org.in', 'published' => '—', 'status' => 'Draft'],
                ],
            ],

            'backend.information.ombudsman' => [
                'primary' => 'Add Ombudsman Entry',
                'search'  => 'Search ombudsman content',
                'filters' => [
                    ['label' => 'Category', 'options' => ['Filing a Complaint', 'Jurisdiction', 'Appeals']],
                    ['label' => 'Status', 'options' => ['Published', 'Draft']],
                ],
                'columns' => [
                    ['key' => 'title', 'label' => 'Title', 'type' => 'strong'],
                    ['key' => 'category', 'label' => 'Category', 'type' => 'chip'],
                    ['key' => 'description', 'label' => 'Description', 'type' => 'muted'],
                    ['key' => 'source', 'label' => 'Official Resource'],
                    ['key' => 'published', 'label' => 'Published Date'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
                ],
                'rows' => [
                    ['title' => 'How to file a complaint with the RBI Ombudsman', 'category' => 'Filing a Complaint', 'description' => 'CMS portal walkthrough and required documents.', 'source' => 'cms.rbi.org.in', 'published' => '17 Sep 2026', 'status' => 'Published'],
                    ['title' => 'What the Ombudsman can and cannot take up', 'category' => 'Jurisdiction', 'description' => 'Grounds of complaint under the integrated scheme.', 'source' => 'rbi.org.in', 'published' => '13 Sep 2026', 'status' => 'Published'],
                    ['title' => 'Appealing an Ombudsman award', 'category' => 'Appeals', 'description' => 'Timelines and the appellate authority process.', 'source' => 'rbi.org.in', 'published' => '08 Sep 2026', 'status' => 'Published'],
                ],
            ],

            'backend.information.financial' => [
                'primary' => 'Add Article',
                'search'  => 'Search financial information',
                'filters' => [
                    ['label' => 'Category', 'options' => ['Investing', 'Taxation', 'Insurance', 'Credit']],
                    ['label' => 'Status', 'options' => ['Published', 'Draft']],
                ],
                'columns' => [
                    ['key' => 'title', 'label' => 'Title', 'type' => 'strong'],
                    ['key' => 'category', 'label' => 'Category', 'type' => 'chip'],
                    ['key' => 'author', 'label' => 'Author', 'type' => 'entity'],
                    ['key' => 'published', 'label' => 'Published Date'],
                    ['key' => 'views', 'label' => 'Views', 'type' => 'amount'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
                ],
                'rows' => [
                    ['title' => 'Understanding the new tax regime slabs', 'category' => 'Taxation', 'author' => 'Priya Menon', 'author_sub' => 'Content Admin', 'published' => '19 Sep 2026', 'views' => '8,412', 'status' => 'Published'],
                    ['title' => 'SIP vs lumpsum: which suits your goal', 'category' => 'Investing', 'author' => 'Karthik Subramanian', 'author_sub' => 'Content Admin', 'published' => '15 Sep 2026', 'views' => '12,905', 'status' => 'Published'],
                    ['title' => 'How your CIBIL score is actually calculated', 'category' => 'Credit', 'author' => 'Priya Menon', 'author_sub' => 'Content Admin', 'published' => '12 Sep 2026', 'views' => '9,730', 'status' => 'Published'],
                    ['title' => 'Should you prepay your home loan or invest?', 'category' => 'Credit', 'author' => 'Karthik Subramanian', 'author_sub' => 'Content Admin', 'published' => '09 Sep 2026', 'views' => '14,208', 'status' => 'Published'],
                    ['title' => 'Term insurance cover: how much is enough', 'category' => 'Insurance', 'author' => 'Karthik Subramanian', 'author_sub' => 'Content Admin', 'published' => '—', 'views' => '0', 'status' => 'Draft'],
                ],
            ],

            'backend.information.compliance' => [
                'primary' => 'Add Compliance Note',
                'search'  => 'Search compliance notes',
                'filters' => [
                    ['label' => 'Category', 'options' => ['SEBI', 'RBI', 'IRDAI', 'Income Tax']],
                    ['label' => 'Status', 'options' => ['Published', 'Draft']],
                ],
                'columns' => [
                    ['key' => 'title', 'label' => 'Title', 'type' => 'strong'],
                    ['key' => 'category', 'label' => 'Category', 'type' => 'chip'],
                    ['key' => 'description', 'label' => 'Description', 'type' => 'muted'],
                    ['key' => 'source', 'label' => 'Source'],
                    ['key' => 'published', 'label' => 'Published Date'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
                ],
                'rows' => [
                    ['title' => 'Nomination mandatory for demat accounts', 'category' => 'SEBI', 'description' => 'Deadline and the opt-out declaration process.', 'source' => 'sebi.gov.in', 'published' => '18 Sep 2026', 'status' => 'Published'],
                    ['title' => 'Annual Information Statement changes', 'category' => 'Income Tax', 'description' => 'New reporting heads added to the AIS for FY 2026-27.', 'source' => 'incometax.gov.in', 'published' => '14 Sep 2026', 'status' => 'Published'],
                    ['title' => 'Bima Sugam onboarding requirements', 'category' => 'IRDAI', 'description' => 'Insurer participation timelines on the unified marketplace.', 'source' => 'irdai.gov.in', 'published' => '09 Sep 2026', 'status' => 'Published'],
                ],
            ],

            'backend.information.economic' => [
                'primary' => 'Add Indicator',
                'search'  => 'Search indicator',
                'filters' => [
                    ['label' => 'Category', 'options' => ['Policy', 'Inflation', 'Growth', 'External']],
                    ['label' => 'Status', 'options' => ['Active', 'Inactive']],
                ],
                'columns' => [
                    ['key' => 'indicator', 'label' => 'Indicator', 'type' => 'strong'],
                    ['key' => 'value', 'label' => 'Value', 'type' => 'amount'],
                    ['key' => 'unit', 'label' => 'Unit', 'type' => 'muted'],
                    ['key' => 'change', 'label' => 'Change', 'type' => 'delta'],
                    ['key' => 'source', 'label' => 'Source'],
                    ['key' => 'updated', 'label' => 'Last Updated', 'type' => 'muted'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
                ],
                'rows' => [
                    ['indicator' => 'Repo Rate', 'value' => '6.50', 'unit' => '%', 'change' => '0.00', 'source' => 'RBI', 'updated' => '20 Sep 2026', 'status' => 'Active'],
                    ['indicator' => 'CPI Inflation', 'value' => '4.87', 'unit' => '%', 'change' => '-0.22', 'source' => 'MoSPI', 'updated' => '12 Sep 2026', 'status' => 'Active'],
                    ['indicator' => 'GDP Growth (Q1)', 'value' => '7.80', 'unit' => '%', 'change' => '+0.40', 'source' => 'MoSPI', 'updated' => '31 Aug 2026', 'status' => 'Active'],
                    ['indicator' => 'Forex Reserves', 'value' => '642.49', 'unit' => 'US$ bn', 'change' => '+2.83', 'source' => 'RBI', 'updated' => '19 Sep 2026', 'status' => 'Active'],
                    ['indicator' => 'WPI Inflation', 'value' => '1.31', 'unit' => '%', 'change' => '-0.18', 'source' => 'DPIIT', 'updated' => '14 Sep 2026', 'status' => 'Active'],
                ],
            ],

            /* -------------------------------- Cyber crime & help center -- */

            'backend.cyber-safety.help' => static::safetyTable('Add Help Article', [
                ['title' => 'Money debited through UPI without approval', 'category' => 'UPI Fraud', 'description' => 'Immediate steps, bank escalation and the 1930 helpline.', 'resource' => 'cybercrime.gov.in', 'published' => '19 Sep 2026', 'status' => 'Published'],
                ['title' => 'Unauthorised card transaction — what to do first', 'category' => 'Card Fraud', 'description' => 'Zero-liability window and how to report within 3 days.', 'resource' => 'rbi.org.in', 'published' => '15 Sep 2026', 'status' => 'Published'],
                ['title' => 'Account taken over after SIM swap', 'category' => 'Account Security', 'description' => 'Recovering access and locking down the account.', 'resource' => 'cybercrime.gov.in', 'published' => '11 Sep 2026', 'status' => 'Published'],
                ['title' => 'Filing an FIR for an online financial fraud', 'category' => 'Banking Fraud', 'description' => 'Documents to carry and the National Cybercrime portal flow.', 'resource' => 'cybercrime.gov.in', 'published' => '—', 'status' => 'Draft'],
            ]),

            'backend.cyber-safety.fraud-awareness' => static::safetyTable('Add Awareness Entry', [
                ['title' => 'Fake loan apps: how to spot one', 'category' => 'Loan Scam', 'description' => 'RBI-registered lender checks and permission red flags.', 'resource' => 'rbi.org.in', 'published' => '18 Sep 2026', 'status' => 'Published'],
                ['title' => 'KYC-expiry SMS phishing', 'category' => 'Phishing', 'description' => 'Why banks never ask for KYC over a link.', 'resource' => 'cybercrime.gov.in', 'published' => '14 Sep 2026', 'status' => 'Published'],
                ['title' => 'Guaranteed-return investment schemes', 'category' => 'Investment Scam', 'description' => 'How Ponzi structures are marketed on social media.', 'resource' => 'sebi.gov.in', 'published' => '10 Sep 2026', 'status' => 'Published'],
                ['title' => 'Customer-care number spoofing on search engines', 'category' => 'Banking Fraud', 'description' => 'Always take helpline numbers from the bank app.', 'resource' => 'rbi.org.in', 'published' => '06 Sep 2026', 'status' => 'Published'],
            ]),

            'backend.cyber-safety.banking-safety' => static::safetyTable('Add Safety Guide', [
                ['title' => 'Safe UPI habits checklist', 'category' => 'UPI Fraud', 'description' => 'Never enter a PIN to receive money — and six more rules.', 'resource' => 'npci.org.in', 'published' => '17 Sep 2026', 'status' => 'Published'],
                ['title' => 'Securing net banking on shared devices', 'category' => 'Account Security', 'description' => 'Session hygiene, virtual keyboards and logout discipline.', 'resource' => 'rbi.org.in', 'published' => '13 Sep 2026', 'status' => 'Published'],
                ['title' => 'Setting transaction limits on cards', 'category' => 'Card Fraud', 'description' => 'Channel-wise controls in the bank app.', 'resource' => 'rbi.org.in', 'published' => '07 Sep 2026', 'status' => 'Published'],
            ]),

            'backend.cyber-safety.scam-awareness' => static::safetyTable('Add Scam Alert', [
                ['title' => 'Digital-arrest video call scam', 'category' => 'Online Scam', 'description' => 'Fake law-enforcement calls demanding immediate transfer.', 'resource' => 'cybercrime.gov.in', 'published' => '20 Sep 2026', 'status' => 'Published'],
                ['title' => 'Part-time job / task-based scam', 'category' => 'Online Scam', 'description' => 'Small payouts first, large deposits demanded later.', 'resource' => 'cybercrime.gov.in', 'published' => '16 Sep 2026', 'status' => 'Published'],
                ['title' => 'Fake electricity-bill disconnection SMS', 'category' => 'Phishing', 'description' => 'Remote-access apps installed under the pretext of payment.', 'resource' => 'cybercrime.gov.in', 'published' => '12 Sep 2026', 'status' => 'Published'],
            ]),

            'backend.cyber-safety.help-center' => [
                'primary' => 'Add Help Topic',
                'search'  => 'Search help topics',
                'filters' => [
                    ['label' => 'Category', 'options' => ['Getting Started', 'Rates', 'Calculators', 'Account', 'Reporting Fraud']],
                    ['label' => 'Status', 'options' => ['Published', 'Draft']],
                ],
                'columns' => [
                    ['key' => 'title', 'label' => 'Topic', 'type' => 'strong'],
                    ['key' => 'category', 'label' => 'Category', 'type' => 'chip'],
                    ['key' => 'description', 'label' => 'Summary', 'type' => 'muted'],
                    ['key' => 'channel', 'label' => 'Escalates To'],
                    ['key' => 'views', 'label' => 'Views', 'type' => 'amount'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
                ],
                'rows' => [
                    ['title' => 'Report a fraudulent transaction', 'category' => 'Reporting Fraud', 'description' => 'Guided flow to the 1930 helpline and the cybercrime portal.', 'channel' => 'Support desk', 'views' => '6,420', 'status' => 'Published'],
                    ['title' => 'Why is my city rate different?', 'category' => 'Rates', 'description' => 'Explains city-wise making charges and local levies.', 'channel' => 'Self-service', 'views' => '4,180', 'status' => 'Published'],
                    ['title' => 'How to save a calculation', 'category' => 'Calculators', 'description' => 'Saving, renaming and sharing calculator results.', 'channel' => 'Self-service', 'views' => '3,905', 'status' => 'Published'],
                    ['title' => 'Delete my Bfinz account', 'category' => 'Account', 'description' => 'Account deletion request and data retention policy.', 'channel' => 'Support desk', 'views' => '1,240', 'status' => 'Published'],
                    ['title' => 'Getting started with goals', 'category' => 'Getting Started', 'description' => 'Walkthrough of creating a first goal plan.', 'channel' => 'Self-service', 'views' => '0', 'status' => 'Draft'],
                ],
            ],

            /* ---------------------------------------------------- Users -- */

            'backend.users.index' => [
                'primary' => 'Add User',
                'search'  => 'Search by name, email or mobile',
                'total'   => 12540,
                'filters' => [
                    ['label' => 'Status', 'options' => ['Active', 'Inactive', 'Pending']],
                    ['label' => 'Registered', 'options' => ['Last 7 days', 'Last 30 days', 'This year']],
                ],
                'columns' => [
                    ['key' => 'id', 'label' => 'User ID', 'type' => 'mono'],
                    ['key' => 'name', 'label' => 'Name', 'type' => 'entity'],
                    ['key' => 'mobile', 'label' => 'Mobile'],
                    ['key' => 'registered', 'label' => 'Registration Date'],
                    ['key' => 'last_login', 'label' => 'Last Login', 'type' => 'muted'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
                ],
                'rows' => [
                    ['id' => 'BFZ-10241', 'name' => 'Arun Prakash', 'name_sub' => 'arun.prakash@gmail.com', 'mobile' => '+91 98407 22135', 'registered' => '04 Sep 2026', 'last_login' => '2 hours ago', 'status' => 'Active'],
                    ['id' => 'BFZ-10240', 'name' => 'Divya Ramesh', 'name_sub' => 'divya.ramesh@outlook.com', 'mobile' => '+91 90031 55420', 'registered' => '03 Sep 2026', 'last_login' => '5 hours ago', 'status' => 'Active'],
                    ['id' => 'BFZ-10239', 'name' => 'Mohammed Irfan', 'name_sub' => 'm.irfan@yahoo.in', 'mobile' => '+91 88254 71903', 'registered' => '02 Sep 2026', 'last_login' => 'Yesterday', 'status' => 'Active'],
                    ['id' => 'BFZ-10238', 'name' => 'Sneha Kulkarni', 'name_sub' => 'sneha.k@gmail.com', 'mobile' => '+91 97865 30118', 'registered' => '01 Sep 2026', 'last_login' => '3 days ago', 'status' => 'Inactive'],
                    ['id' => 'BFZ-10237', 'name' => 'Rajesh Nair', 'name_sub' => 'rajesh.nair@gmail.com', 'mobile' => '+91 99620 84471', 'registered' => '29 Aug 2026', 'last_login' => '1 week ago', 'status' => 'Active'],
                    ['id' => 'BFZ-10236', 'name' => 'Pooja Agarwal', 'name_sub' => 'pooja.agarwal@gmail.com', 'mobile' => '+91 93810 29764', 'registered' => '27 Aug 2026', 'last_login' => 'Never', 'status' => 'Pending'],
                ],
            ],

            'backend.users.activity' => [
                'search'  => 'Search user activity',
                'filters' => [
                    ['label' => 'Feature', 'options' => ['Calculators', 'Market', 'Comparison', 'Banking']],
                    ['label' => 'Period', 'options' => ['Today', 'Last 7 days', 'Last 30 days']],
                ],
                'columns' => [
                    ['key' => 'user', 'label' => 'User', 'type' => 'entity'],
                    ['key' => 'action', 'label' => 'Activity', 'type' => 'strong'],
                    ['key' => 'module', 'label' => 'Module'],
                    ['key' => 'device', 'label' => 'Device', 'type' => 'muted'],
                    ['key' => 'time', 'label' => 'Time', 'type' => 'muted'],
                ],
                'rows' => [
                    ['user' => 'Arun Prakash', 'user_sub' => 'BFZ-10241', 'action' => 'Calculated home loan EMI', 'module' => 'EMI Calculator', 'device' => 'Android 14', 'time' => 'Today, 09:42 AM'],
                    ['user' => 'Divya Ramesh', 'user_sub' => 'BFZ-10240', 'action' => 'Viewed 22K gold rate — Chennai', 'module' => 'Market Overview', 'device' => 'iOS 18', 'time' => 'Today, 09:20 AM'],
                    ['user' => 'Mohammed Irfan', 'user_sub' => 'BFZ-10239', 'action' => 'Compared 4 personal loan offers', 'module' => 'Comparison', 'device' => 'Android 13', 'time' => 'Today, 08:55 AM'],
                    ['user' => 'Rajesh Nair', 'user_sub' => 'BFZ-10237', 'action' => 'Searched IFSC — HDFC0000521', 'module' => 'Banking Utilities', 'device' => 'Android 14', 'time' => 'Yesterday, 07:11 PM'],
                    ['user' => 'Sneha Kulkarni', 'user_sub' => 'BFZ-10238', 'action' => 'Created a retirement goal plan', 'module' => 'Goal Management', 'device' => 'iOS 17', 'time' => 'Yesterday, 06:02 PM'],
                ],
            ],

            'backend.users.feedback' => [
                'search'  => 'Search feedback',
                'filters' => [
                    ['label' => 'Rating', 'options' => ['5 star', '4 star', '3 star', 'Below 3']],
                    ['label' => 'Status', 'options' => ['Open', 'Reviewed', 'Resolved']],
                ],
                'columns' => [
                    ['key' => 'user', 'label' => 'User', 'type' => 'entity'],
                    ['key' => 'rating', 'label' => 'Rating', 'type' => 'rating'],
                    ['key' => 'comment', 'label' => 'Feedback', 'type' => 'strong'],
                    ['key' => 'module', 'label' => 'Area'],
                    ['key' => 'date', 'label' => 'Date', 'type' => 'muted'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
                ],
                'rows' => [
                    ['user' => 'Divya Ramesh', 'user_sub' => 'BFZ-10240', 'rating' => 5, 'comment' => 'Gold rate updates are faster than any other app I use.', 'module' => 'Market Overview', 'date' => '04 Sep 2026', 'status' => 'Reviewed'],
                    ['user' => 'Arun Prakash', 'user_sub' => 'BFZ-10241', 'rating' => 4, 'comment' => 'EMI calculator is great. Please add a prepayment option.', 'module' => 'Financial Tools', 'date' => '03 Sep 2026', 'status' => 'Open'],
                    ['user' => 'Rajesh Nair', 'user_sub' => 'BFZ-10237', 'rating' => 5, 'comment' => 'IFSC search saved me a trip to the branch.', 'module' => 'Banking Utilities', 'date' => '01 Sep 2026', 'status' => 'Resolved'],
                    ['user' => 'Sneha Kulkarni', 'user_sub' => 'BFZ-10238', 'rating' => 3, 'comment' => 'Fuel prices for Pune were a day behind last week.', 'module' => 'Market Overview', 'date' => '28 Aug 2026', 'status' => 'Resolved'],
                ],
            ],

            'backend.users.support' => [
                'search'  => 'Search ticket ID or subject',
                'filters' => [
                    ['label' => 'Status', 'options' => ['Open', 'Pending', 'Resolved']],
                    ['label' => 'Priority', 'options' => ['High', 'Medium', 'Low']],
                ],
                'columns' => [
                    ['key' => 'ticket', 'label' => 'Ticket', 'type' => 'mono'],
                    ['key' => 'user', 'label' => 'Raised By', 'type' => 'entity'],
                    ['key' => 'subject', 'label' => 'Subject', 'type' => 'strong'],
                    ['key' => 'priority', 'label' => 'Priority', 'type' => 'badge'],
                    ['key' => 'created', 'label' => 'Created', 'type' => 'muted'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
                ],
                'rows' => [
                    ['ticket' => 'SUP-4412', 'user' => 'Mohammed Irfan', 'user_sub' => 'BFZ-10239', 'subject' => 'Unable to view FD rates for Federal Bank', 'priority' => 'High', 'created' => '04 Sep 2026', 'status' => 'Open'],
                    ['ticket' => 'SUP-4411', 'user' => 'Pooja Agarwal', 'user_sub' => 'BFZ-10236', 'subject' => 'OTP not received during registration', 'priority' => 'High', 'created' => '03 Sep 2026', 'status' => 'Pending'],
                    ['ticket' => 'SUP-4409', 'user' => 'Arun Prakash', 'user_sub' => 'BFZ-10241', 'subject' => 'Gold rate shows old value after refresh', 'priority' => 'Medium', 'created' => '02 Sep 2026', 'status' => 'Resolved'],
                    ['ticket' => 'SUP-4407', 'user' => 'Divya Ramesh', 'user_sub' => 'BFZ-10240', 'subject' => 'Request to add Coimbatore fuel prices', 'priority' => 'Low', 'created' => '30 Aug 2026', 'status' => 'Resolved'],
                ],
            ],

            /* --------------------------------------- Admin management -- */

            'backend.admin-management.admins' => [
                'primary' => 'Add Admin User',
                'search'  => 'Search admin name or email',
                'filters' => [
                    ['label' => 'Role', 'options' => ['Super Admin', 'Data Admin', 'Content Admin', 'Support Admin', 'Analyst']],
                    ['label' => 'Status', 'options' => ['Active', 'Inactive']],
                ],
                'columns' => [
                    ['key' => 'name', 'label' => 'Name', 'type' => 'entity'],
                    ['key' => 'role', 'label' => 'Role', 'type' => 'chip'],
                    ['key' => 'last_login', 'label' => 'Last Login', 'type' => 'muted'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
                ],
                'rows' => [
                    ['name' => 'Admin', 'name_sub' => 'admin@gmail.com', 'role' => 'Super Admin', 'last_login' => 'Today, 09:12 AM', 'status' => 'Active'],
                    ['name' => 'Priya Menon', 'name_sub' => 'priya.menon@bfinz.com', 'role' => 'Content Admin', 'last_login' => 'Today, 08:40 AM', 'status' => 'Active'],
                    ['name' => 'Karthik Subramanian', 'name_sub' => 'karthik.s@bfinz.com', 'role' => 'Content Admin', 'last_login' => 'Yesterday, 06:22 PM', 'status' => 'Active'],
                    ['name' => 'Nandini Rao', 'name_sub' => 'nandini.rao@bfinz.com', 'role' => 'Data Admin', 'last_login' => 'Yesterday, 11:05 AM', 'status' => 'Active'],
                    ['name' => 'Vikram Shetty', 'name_sub' => 'vikram.shetty@bfinz.com', 'role' => 'Support Admin', 'last_login' => '3 days ago', 'status' => 'Active'],
                    ['name' => 'Anitha Raj', 'name_sub' => 'anitha.raj@bfinz.com', 'role' => 'Analyst', 'last_login' => '2 weeks ago', 'status' => 'Inactive'],
                ],
            ],

            'backend.admin-management.activity-logs' => [
                'search'  => 'Search admin activity',
                'filters' => [
                    ['label' => 'Admin', 'options' => ['Admin', 'Priya Menon', 'Nandini Rao']],
                    ['label' => 'Module', 'options' => ['Market Overview', 'Banking Utilities', 'Information', 'Comparison']],
                    ['label' => 'Date', 'options' => ['Today', 'Last 7 days', 'Last 30 days']],
                ],
                'columns' => [
                    ['key' => 'admin', 'label' => 'Admin', 'type' => 'entity'],
                    ['key' => 'action', 'label' => 'Action', 'type' => 'strong'],
                    ['key' => 'module', 'label' => 'Module', 'type' => 'chip'],
                    ['key' => 'description', 'label' => 'Description', 'type' => 'muted'],
                    ['key' => 'date', 'label' => 'Date'],
                    ['key' => 'time', 'label' => 'Time', 'type' => 'mono'],
                    ['key' => 'ip', 'label' => 'IP', 'type' => 'mono'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
                ],
                'rows' => [
                    ['admin' => 'Nandini Rao', 'admin_sub' => 'Data Admin', 'action' => 'Updated Gold Rate', 'module' => 'Market Overview', 'description' => 'Chennai 24K changed to ₹6,850', 'date' => '22 Sep 2026', 'time' => '09:42 AM', 'ip' => '103.21.58.14', 'status' => 'Success'],
                    ['admin' => 'Admin', 'admin_sub' => 'Super Admin', 'action' => 'Added new bank', 'module' => 'Banking Utilities', 'description' => 'AU Small Finance Bank created', 'date' => '22 Sep 2026', 'time' => '09:15 AM', 'ip' => '103.21.58.14', 'status' => 'Success'],
                    ['admin' => 'Priya Menon', 'admin_sub' => 'Content Admin', 'action' => 'Published RBI update', 'module' => 'Information', 'description' => 'Repo rate held at 6.50%', 'date' => '21 Sep 2026', 'time' => '04:28 PM', 'ip' => '49.207.112.90', 'status' => 'Success'],
                    ['admin' => 'Nandini Rao', 'admin_sub' => 'Data Admin', 'action' => 'Updated loan comparison', 'module' => 'Comparison', 'description' => 'SBI Home Loan set to 8.40%', 'date' => '21 Sep 2026', 'time' => '11:02 AM', 'ip' => '103.21.58.14', 'status' => 'Success'],
                    ['admin' => 'Vikram Shetty', 'admin_sub' => 'Support Admin', 'action' => 'Failed login attempt', 'module' => 'Admin Management', 'description' => 'Incorrect password (3rd attempt)', 'date' => '20 Sep 2026', 'time' => '08:11 PM', 'ip' => '157.32.44.201', 'status' => 'Failed'],
                ],
            ],

            default => null,
        };
    }

    /** Cyber-safety pages all share one shape; only the rows differ. */
    private static function safetyTable(string $primary, array $rows): array
    {
        return [
            'primary' => $primary,
            'search'  => 'Search title or category',
            'filters' => [
                ['label' => 'Category', 'options' => [
                    'UPI Fraud', 'Banking Fraud', 'Card Fraud', 'Phishing',
                    'Investment Scam', 'Loan Scam', 'Online Scam', 'Account Security',
                ]],
                ['label' => 'Status', 'options' => ['Published', 'Draft']],
            ],
            'columns' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'strong'],
                ['key' => 'category', 'label' => 'Category', 'type' => 'chip'],
                ['key' => 'description', 'label' => 'Description', 'type' => 'muted'],
                ['key' => 'resource', 'label' => 'Official Resource'],
                ['key' => 'published', 'label' => 'Published Date'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
            ],
            'rows' => $rows,
        ];
    }
}
