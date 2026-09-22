{{--
|--------------------------------------------------------------------------
| Backend (Admin Panel) Master Layout
|--------------------------------------------------------------------------
|
| Every admin page extends this file:
|
|   @extends('backend.template.layouts.template-base')
|   @section('content') ... @endsection
|   @push('styles') ... @endpush   (page-specific CSS)
|   @push('scripts') ... @endpush  (page-specific JS)
|
| The page title and breadcrumb are not set per page — they come from the
| $page / $breadcrumb variables the controller passes, which both originate in
| App\Support\AdminMenu.
|
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('backend.template.layouts.meta-tags')

    <title>{{ $page['label'] ?? 'Admin' }} &middot; Bfinz Admin</title>

    @include('backend.template.layouts.common-css')
    @stack('styles')
</head>
<body>

    <div class="app">

        @include('backend.template.layouts.sidebar')

        {{-- Closes the mobile drawer when the dimmed area is tapped. --}}
        <div class="app__backdrop" data-drawer-close aria-hidden="true"></div>

        <div class="app__main">
            @include('backend.template.layouts.header')

            <main class="app__content" id="main-content">
                @include('backend.template.partials.flash')

                @yield('content')
            </main>

            @include('backend.template.layouts.footer')
        </div>

    </div>

    {{-- Toasts are injected here by app.js. --}}
    <div class="toasts" aria-live="polite" aria-atomic="false"></div>

    @include('backend.template.layouts.common-js')
    @stack('scripts')
</body>
</html>
