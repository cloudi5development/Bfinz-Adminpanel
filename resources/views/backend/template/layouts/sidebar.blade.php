{{--
    Admin side navigation.

    The whole tree is rendered from App\Support\AdminMenu — the same registry
    routes/admin.php generates routes from. A link here therefore always has a
    route behind it, and adding a page to the registry adds it to the menu.

    Sections whose module the signed-in admin cannot open are skipped, so the
    menu shows only what that account may actually reach.
--}}

@php
    use App\Support\AdminAuth;
    use App\Support\AdminMenu;
    use App\Support\AdminModules;

    $current  = request()->route()?->getName();
    $sections = AdminMenu::sections();
    $admin    = AdminAuth::user();
@endphp

<aside class="sidebar" id="sidebar">

    <div class="sidebar__brand">
        <a href="{{ route('backend.dashboard') }}" class="sidebar__logo" aria-label="Bfinz — admin dashboard">
            {{-- The 420px copy, not the 4672px master: this loads on every admin page. --}}
            <img src="{{ asset('assets/img/bfinz-logo-sm.png') }}" alt="Bfinz" width="66" height="21">
        </a>

        {{-- Collapsed mode shows a compact mark in place of the wordmark. --}}
        <span class="sidebar__mark" aria-hidden="true">B</span>

        <button type="button" class="sidebar__collapse u-hide-mobile" data-sidebar-toggle
                aria-label="Collapse or expand the sidebar" title="Toggle sidebar">
            <x-admin.icon name="menu" />
        </button>
    </div>

    <nav class="sidebar__nav" aria-label="Admin sections">
        <ul>
            @foreach ($sections as $key => $section)

                @php
                    // Hide a whole section the account has no access to. The
                    // dashboard has no module and is open to every admin.
                    $module = $section['module'];
                    $gated  = AdminModules::isGrantable($module) || AdminModules::isSuperAdminOnly($module);

                    if ($gated && $admin && ! $admin->canAccessModule($module)) {
                        continue;
                    }

                    $children = array_values(array_filter(
                        $section['children'],
                        fn ($child) => empty($child['hidden'])
                    ));

                    $isActive = AdminMenu::sectionIsActive($key, $current);
                @endphp

                {{-- Band headings come from the registry, so regrouping the
                     menu never means editing this file. --}}
                @if (! empty($section['heading']))
                    <li class="nav-heading" aria-hidden="true">{{ $section['heading'] }}</li>
                @endif

                <li class="nav-item {{ $isActive ? 'is-open' : '' }}">

                    @if (! empty($section['single']))
                        {{-- Dashboard: a direct link, no submenu. --}}
                        <a href="{{ route('backend.dashboard') }}"
                           class="nav-link {{ $isActive ? 'is-active' : '' }}"
                           data-tip="{{ $section['label'] }}"
                           @if ($isActive) aria-current="page" @endif>
                            <span class="nav-link__icon"><x-admin.icon :name="$section['icon']" /></span>
                            <span class="nav-link__text">{{ $section['label'] }}</span>
                        </a>
                    @else
                        <button type="button"
                                class="nav-link {{ $isActive ? 'is-active' : '' }}"
                                data-nav-toggle
                                data-tip="{{ $section['label'] }}"
                                aria-expanded="{{ $isActive ? 'true' : 'false' }}">
                            <span class="nav-link__icon"><x-admin.icon :name="$section['icon']" /></span>
                            <span class="nav-link__text">{{ $section['label'] }}</span>
                            <x-admin.icon name="chevron-right" class="nav-link__caret" />
                        </button>

                        <div class="nav-sub" data-title="{{ $section['label'] }}">
                            <div class="nav-sub__inner">
                                @foreach ($children as $child)
                                    @php $route = 'backend.' . $module . '.' . $child['name']; @endphp
                                    <a href="{{ route($route) }}"
                                       class="{{ $current === $route ? 'is-active' : '' }}"
                                       @if ($current === $route) aria-current="page" @endif>
                                        {{ $child['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </li>
            @endforeach
        </ul>
    </nav>

    <div class="sidebar__foot">
        <a href="{{ route('backend.settings.general') }}" class="sidebar__action" data-tip="Settings">
            <x-admin.icon name="settings" />
            <span>Settings</span>
        </a>
    </div>

</aside>
