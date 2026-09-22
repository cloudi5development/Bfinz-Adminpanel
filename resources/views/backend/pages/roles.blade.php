{{--
    Admin Management — Roles & Permissions.

    The permission grid is rendered from App\Support\AdminModules, the same
    registry the EnsureModuleAccess middleware enforces against. So the
    checkboxes below describe the access model the panel already runs on, ready
    for the day roles become editable records rather than a fixed list.
--}}
@extends('backend.template.layouts.template-base')

@php
    use App\Support\AdminModules;
    use App\Support\MockData;

    $roles  = MockData::roles();
    $groups = AdminModules::GROUPS;
@endphp

@section('content')

    <x-admin.page-header
        title="Roles & Permissions"
        description="What each kind of admin account may open. Permissions are enforced per module by the route-level middleware.">
        <button type="button" class="btn btn--primary" data-modal-open="roleAddModal">
            <x-admin.icon name="plus" /> Add Role
        </button>
    </x-admin.page-header>

    <div class="grid grid--3 u-mb-3">
        @foreach (array_slice($roles, 0, 3) as $role)
            <div class="card">
                <div class="card__body">
                    <div class="u-between">
                        <div class="u-row u-row--sm">
                            <span class="stat__icon stat__icon--{{ $loop->first ? 'primary' : 'royal' }}" aria-hidden="true">
                                <x-admin.icon name="shield-user" />
                            </span>
                            <div>
                                <div class="u-strong">{{ $role['name'] }}</div>
                                <div class="u-xs u-muted">{{ $role['admins'] }} {{ Str::plural('account', $role['admins']) }}</div>
                            </div>
                        </div>
                        @if ($role['granted'] === 'all')
                            <span class="badge badge--info">Full access</span>
                        @endif
                    </div>
                    <p class="u-small u-muted u-mt-2">{{ $role['description'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Permission matrix --------------------------------------------- --}}
    <div class="card">
        <div class="card__head">
            <div>
                <h2 class="card__title">Permission Matrix</h2>
                <p class="card__desc">A ticked box means that role may open every page in the module.</p>
            </div>
            <button type="button" class="btn btn--primary btn--sm"
                    data-toast="Permissions saved"
                    data-toast-body="Role editing becomes persistent when roles move into the database."
                    data-toast-tone="success">
                <x-admin.icon name="save" /> Save permissions
            </button>
        </div>

        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th scope="col">Module</th>
                        @foreach ($roles as $role)
                            <th scope="col" class="u-center">{{ $role['name'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    {{-- Dashboard is open to every signed-in admin. --}}
                    <tr>
                        <td class="is-strong">Dashboard</td>
                        @foreach ($roles as $role)
                            <td class="u-center">
                                <label class="check" style="justify-content:center">
                                    <span class="visually-hidden">Dashboard for {{ $role['name'] }}</span>
                                    <input type="checkbox" checked disabled>
                                </label>
                            </td>
                        @endforeach
                    </tr>

                    @foreach ($groups as $groupName => $modules)
                        <tr>
                            <td colspan="{{ count($roles) + 1 }}"
                                style="background:#FBFCFE;font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--text-2)">
                                {{ $groupName }}
                            </td>
                        </tr>

                        @foreach ($modules as $key => $label)
                            <tr>
                                <td class="is-strong">{{ $label }}</td>
                                @foreach ($roles as $role)
                                    @php
                                        $granted = $role['granted'] === 'all'
                                            || in_array($key, (array) $role['granted'], true);
                                    @endphp
                                    <td class="u-center">
                                        <label class="check" style="justify-content:center">
                                            <span class="visually-hidden">{{ $label }} for {{ $role['name'] }}</span>
                                            <input type="checkbox" @checked($granted)
                                                   @disabled($role['granted'] === 'all')>
                                        </label>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach

                    {{-- Reserved for the owner of the panel. --}}
                    <tr>
                        <td class="is-strong">
                            Admin Management
                            <span class="chip u-mt-1">Super Admin only</span>
                        </td>
                        @foreach ($roles as $role)
                            <td class="u-center">
                                <label class="check" style="justify-content:center">
                                    <span class="visually-hidden">Admin Management for {{ $role['name'] }}</span>
                                    <input type="checkbox" @checked($role['granted'] === 'all') disabled>
                                </label>
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="card__foot">
            <span class="table-foot-note">
                Admin Management is never grantable — only the main admin can manage accounts and permissions.
            </span>
        </div>
    </div>

    <x-admin.modal id="roleAddModal" title="Add a role"
                   description="Define a name and pick the modules this role may open.">
        <div class="field">
            <label class="field__label" for="roleName">Role name <span class="field__req">*</span></label>
            <input class="input" id="roleName" placeholder="e.g. Rates Editor">
        </div>
        <div class="field">
            <label class="field__label" for="roleDesc">Description</label>
            <textarea class="textarea" id="roleDesc" placeholder="What this role is responsible for"></textarea>
        </div>
        <div class="field">
            <span class="field__label">Modules</span>
            <div class="grid grid--2" style="gap:8px">
                @foreach (AdminModules::labels() as $key => $label)
                    <label class="check">
                        <input type="checkbox" value="{{ $key }}"> {{ $label }}
                    </label>
                @endforeach
            </div>
        </div>

        <x-slot:footer>
            <button type="button" class="btn btn--ghost" data-modal-close>Cancel</button>
            <button type="button" class="btn btn--primary" data-modal-close
                    data-toast="Role created" data-toast-tone="success">Create role</button>
        </x-slot:footer>
    </x-admin.modal>

@endsection
