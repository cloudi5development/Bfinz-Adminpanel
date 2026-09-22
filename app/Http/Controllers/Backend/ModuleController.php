<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Support\AdminMenu;
use App\Support\MockData;
use App\Support\MockTables;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Renders every admin module page declared in AdminMenu.
 *
 * The page is resolved from the current route name rather than a parameter, so
 * routes/admin.php can register the whole tree without a controller per module.
 * `type` on the menu entry decides which view renders it, and the matching
 * Mock* class supplies the content.
 *
 * When a module graduates to real data it gets its own controller and the menu
 * entry points at it instead — nothing else has to change.
 */
class ModuleController extends Controller
{
    public function __invoke(Request $request): View
    {
        $route = (string) $request->route()->getName();
        $page  = AdminMenu::page($route);

        abort_if($page === null, 404);

        $shared = [
            'page'       => $page,
            'breadcrumb' => AdminMenu::breadcrumb($route),
        ];

        return match ($page['type']) {
            'calculator' => view('backend.pages.calculator', $shared + ['tool' => MockData::calculator($page['name'])]),
            'goal'       => view('backend.pages.goal',       $shared + ['goal' => MockData::goal($page['name'])]),
            'custom'     => view($page['view'],              $shared),
            default      => view('backend.pages.table',      $shared + ['table' => MockTables::for($route)]),
        };
    }
}
