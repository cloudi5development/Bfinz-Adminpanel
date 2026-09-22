{{--
    Admin topbar: breadcrumb + page title on the left, search / notifications /
    profile on the right. The breadcrumb comes from AdminMenu so it always
    matches the sidebar.
--}}

@php
    use App\Support\MockData;

    $notifications = MockData::headerNotifications();
    $adminName     = session('admin_name', 'Admin');
    $adminEmail    = session('admin_email', '');

    $initials = collect(explode(' ', trim($adminName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('') ?: 'A';
@endphp

<header class="topbar">

    {{-- Drawer trigger — only visible below the tablet breakpoint. The desktop
         collapse control lives in the sidebar header instead. --}}
    <button type="button" class="icon-btn u-hide-desktop" data-drawer-toggle aria-label="Open navigation">
        <x-admin.icon name="menu" />
    </button>

    {{-- No breadcrumb: it repeated the page title directly underneath it. The
         sidebar already shows where you are, and the page header carries the
         full name. AdminMenu::breadcrumb() is still there if it is wanted back. --}}
    <div class="topbar__left">
        <div class="topbar__title">{{ $page['label'] ?? 'Dashboard' }}</div>
    </div>

    <div class="topbar__right">

        <div class="topbar__search">
            <x-admin.icon name="search" />
            <label for="globalSearch" class="visually-hidden">Search the admin panel</label>
            <input type="search" id="globalSearch" data-global-search
                   placeholder="Search anything" autocomplete="off">
            <span class="topbar__kbd" aria-hidden="true">/</span>
        </div>

        {{-- Notifications ------------------------------------------------ --}}
        <div class="dropdown" data-dropdown>
            <button type="button" class="icon-btn" data-dropdown-trigger
                    aria-expanded="false" aria-haspopup="true"
                    aria-label="{{ count($notifications) }} unread notifications">
                <x-admin.icon name="bell" />
                <span class="icon-btn__count">{{ count($notifications) }}</span>
            </button>

            <div class="dropdown__menu dropdown__menu--wide" role="menu">
                <div class="dropdown__head">
                    <span class="dropdown__title">Notifications</span>
                    <span class="badge badge--info">{{ count($notifications) }} new</span>
                </div>

                @foreach ($notifications as $note)
                    <div class="notif">
                        <span class="notif__dot notif__dot--{{ $note['tone'] }}" aria-hidden="true"></span>
                        <div>
                            <div class="notif__title">{{ $note['title'] }}</div>
                            <div class="notif__body">{{ $note['body'] }}</div>
                            <div class="notif__time">{{ $note['time'] }}</div>
                        </div>
                    </div>
                @endforeach

                <div class="dropdown__sep"></div>
                <a href="{{ route('backend.users.support') }}" class="dropdown__item">
                    <x-admin.icon name="inbox" /> Open support requests
                </a>
            </div>
        </div>

        {{-- Profile ------------------------------------------------------ --}}
        <div class="dropdown" data-dropdown>
            <button type="button" class="profile-btn" data-dropdown-trigger
                    aria-expanded="false" aria-haspopup="true">
                <span class="avatar" aria-hidden="true">{{ $initials }}</span>
                <span class="u-nowrap">
                    <span class="profile-btn__name">{{ $adminName }}</span><br>
                    <span class="profile-btn__role">Super Admin</span>
                </span>
                <x-admin.icon name="chevron-down" width="15" height="15" style="color:var(--text-3)" />
            </button>

            <div class="dropdown__menu" role="menu">
                <div class="dropdown__head">
                    <div>
                        <div class="dropdown__title">{{ $adminName }}</div>
                        @if ($adminEmail)
                            <div class="u-xs u-muted">{{ $adminEmail }}</div>
                        @endif
                    </div>
                </div>

                <a href="{{ route('backend.admin-management.admins') }}" class="dropdown__item" role="menuitem">
                    <x-admin.icon name="user" /> My Profile
                </a>
                <a href="{{ route('backend.settings.general') }}" class="dropdown__item" role="menuitem">
                    <x-admin.icon name="sliders" /> Preferences
                </a>
                <a href="{{ route('backend.admin-management.activity-logs') }}" class="dropdown__item" role="menuitem">
                    <x-admin.icon name="history" /> Activity
                </a>

                <div class="dropdown__sep"></div>

                <form method="POST" action="{{ route('backend.auth.logout') }}">
                    @csrf
                    <button type="submit" class="dropdown__item dropdown__item--danger" role="menuitem">
                        <x-admin.icon name="logout" /> Logout
                    </button>
                </form>
            </div>
        </div>

    </div>
</header>
