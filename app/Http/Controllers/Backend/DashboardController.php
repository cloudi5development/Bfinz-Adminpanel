<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Support\AdminMenu;
use App\Support\MockData;
use Illuminate\View\View;

/**
 * The Bfinz admin dashboard.
 *
 * Every figure below comes from App\Support\MockData while the panel is in its
 * UI phase. Each call is a one-for-one stand-in for the query or API call that
 * will replace it, so the view never has to change when the real data lands.
 */
class DashboardController extends Controller
{
    public function index(): View
    {
        return view('backend.pages.dashboard', [
            'page'       => AdminMenu::page('backend.dashboard'),
            'breadcrumb' => AdminMenu::breadcrumb('backend.dashboard'),

            'stats'        => MockData::dashboardStats(),
            'statistic'    => MockData::currentStatistic(),
            'market'       => MockData::marketOverview(),
            'topTools'     => MockData::topTools(),
            'topRates'     => MockData::mostViewedRates(),
            'recentUsers'  => MockData::recentUsers(),
            'adminLog'     => MockData::recentAdminActivity(),
            'quickActions' => MockData::quickActions(),
        ]);
    }
}
