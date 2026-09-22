{{--
    User Management — User Details.

    The record behind a row in the Users table: profile, engagement, the goals
    they are tracking and a timeline of what they did in the app.
--}}
@extends('backend.template.layouts.template-base')

@php
    use App\Support\MockData;

    $user = MockData::userDetail();
@endphp

@section('content')

    <x-admin.page-header
        :title="$user['name']"
        :description="'Bfinz app user ' . $user['id'] . ' — profile, engagement and activity.'">
        <a href="{{ route('backend.users.index') }}" class="btn btn--ghost">
            <x-admin.icon name="chevron-left" /> Back to users
        </a>
        <button type="button" class="btn btn--ghost" data-modal-open="userDeactivateModal">
            <x-admin.icon name="lock" /> Deactivate
        </button>
        <button type="button" class="btn btn--primary" data-modal-open="userEditModal">
            <x-admin.icon name="edit" /> Edit user
        </button>
    </x-admin.page-header>

    <div class="grid grid--1-2 u-mb-3">

        {{-- Profile ------------------------------------------------------ --}}
        <div class="card">
            <div class="card__body">
                <div class="u-row" style="gap:14px">
                    <span class="avatar avatar--lg" aria-hidden="true">{{ mb_substr($user['name'], 0, 1) }}</span>
                    <div class="u-grow">
                        <div class="u-strong" style="font-size:16px">{{ $user['name'] }}</div>
                        <div class="u-small u-muted">{{ $user['email'] }}</div>
                    </div>
                    <x-admin.status-badge :status="$user['status']" />
                </div>

                <div class="kv u-mt-3">
                    <div class="kv__row"><span class="kv__k">User ID</span><span class="kv__v u-mono">{{ $user['id'] }}</span></div>
                    <div class="kv__row"><span class="kv__k">Mobile</span><span class="kv__v">{{ $user['mobile'] }}</span></div>
                    <div class="kv__row"><span class="kv__k">Location</span><span class="kv__v">{{ $user['city'] }}</span></div>
                    <div class="kv__row"><span class="kv__k">Registered</span><span class="kv__v">{{ $user['registered'] }}</span></div>
                    <div class="kv__row"><span class="kv__k">Last login</span><span class="kv__v">{{ $user['last_login'] }}</span></div>
                    <div class="kv__row"><span class="kv__k">Device</span><span class="kv__v">{{ $user['device'] }}</span></div>
                </div>
            </div>

            <div class="card__foot">
                <button type="button" class="btn btn--ghost btn--sm">
                    <x-admin.icon name="mail" /> Email user
                </button>
                <button type="button" class="btn btn--ghost btn--sm">
                    <x-admin.icon name="bell" /> Send notification
                </button>
            </div>
        </div>

        <div class="stack">
            <div class="grid grid--4">
                @foreach ($user['stats'] as $stat)
                    <x-admin.stat-card
                        :label="$stat['label']"
                        :value="$stat['value']"
                        icon="activity"
                        :tone="$loop->first ? 'primary' : ($loop->index === 1 ? 'royal' : ($loop->index === 2 ? 'success' : 'warning'))" />
                @endforeach
            </div>

            <div class="card">
                <div class="card__head">
                    <div>
                        <h2 class="card__title">Goal Plans</h2>
                        <p class="card__desc">Goals this user is tracking in the app.</p>
                    </div>
                </div>
                <div class="card__body">
                    @foreach ($user['goals'] as $goal)
                        <div style="padding:11px 0;border-bottom:1px solid var(--border-soft)">
                            <div class="u-between u-mb-2">
                                <span class="u-strong" style="font-size:13px">{{ $goal['name'] }}</span>
                                <span class="u-xs u-muted">{{ $goal['saved'] }} of {{ $goal['target'] }}</span>
                            </div>
                            <div class="progress">
                                <div class="progress__fill" style="width:{{ $goal['progress'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    {{-- Activity timeline ------------------------------------------------ --}}
    <div class="card">
        <div class="card__head">
            <div>
                <h2 class="card__title">Recent Activity</h2>
                <p class="card__desc">What this user did in the app, most recent first.</p>
            </div>
            <a href="{{ route('backend.users.activity') }}" class="btn btn--ghost btn--sm">
                All user activity <x-admin.icon name="arrow-right" />
            </a>
        </div>

        <div class="card__body">
            <div class="timeline">
                @foreach ($user['timeline'] as $entry)
                    <div class="tl">
                        <span class="tl__icon" aria-hidden="true"><x-admin.icon name="activity" /></span>
                        <div>
                            <div class="tl__text">{{ $entry['action'] }}</div>
                            <div class="tl__meta">{{ $entry['detail'] }}</div>
                        </div>
                        <span class="tl__time">{{ $entry['time'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <x-admin.modal id="userEditModal" title="Edit user"
                   description="Profile fields become editable when the module is connected.">
        <div class="field-row">
            <div class="field">
                <label class="field__label" for="userName">Name</label>
                <input class="input" id="userName" value="{{ $user['name'] }}">
            </div>
            <div class="field">
                <label class="field__label" for="userStatus">Status</label>
                <select class="select" id="userStatus">
                    <option>Active</option>
                    <option>Inactive</option>
                    <option>Pending</option>
                </select>
            </div>
        </div>
        <div class="field">
            <label class="field__label" for="userEmail">Email</label>
            <input class="input" type="email" id="userEmail" value="{{ $user['email'] }}">
        </div>
        <div class="field">
            <label class="field__label" for="userMobile">Mobile</label>
            <input class="input" id="userMobile" value="{{ $user['mobile'] }}">
        </div>

        <x-slot:footer>
            <button type="button" class="btn btn--ghost" data-modal-close>Cancel</button>
            <button type="button" class="btn btn--primary" data-modal-close
                    data-toast="User updated" data-toast-tone="success">Save changes</button>
        </x-slot:footer>
    </x-admin.modal>

    <x-admin.confirm-dialog
        id="userDeactivateModal"
        title="Deactivate this user?"
        body="The account stays on record but the user can no longer sign in to the Bfinz app until it is reactivated."
        confirm="Deactivate user" />

@endsection
