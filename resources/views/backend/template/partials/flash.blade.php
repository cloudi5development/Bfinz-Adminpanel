{{--
    Session feedback. EnsureModuleAccess redirects here with "error" when an
    admin opens a module they do not have access to; controllers use "success".
--}}
@if (session('success'))
    <div class="flash flash--success" role="status">
        <x-admin.icon name="check" />
        <div>{{ session('success') }}</div>
    </div>
@endif

@if (session('error'))
    <div class="flash flash--danger" role="alert">
        <x-admin.icon name="alert" />
        <div>{{ session('error') }}</div>
    </div>
@endif
