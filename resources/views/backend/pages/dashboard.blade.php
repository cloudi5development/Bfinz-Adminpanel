{{--
    Bfinz admin dashboard.

    Eight blocks, in the order an operator actually scans them: the headline
    counters, then activity and feature usage, then the ranked lists, then the
    recent records, then system health and the shortcuts.

    All figures come from App\Support\MockData — see DashboardController.
--}}
@extends('backend.template.layouts.template-base')

@section('content')

    {{-- No page header: the topbar already names the page, and the counters
         below say more than a title and a sentence of description would. --}}

    {{-- A. Headline counters ------------------------------------------- --}}
    <div class="kpi-grid">
        @foreach ($stats as $stat)
            <x-admin.kpi-card
                :title="$stat['label']"
                :value="$stat['value']"
                :icon="$stat['icon'] ?? 'chart'"
                :variant="$stat['variant'] ?? 'plain'"
                :route="$stat['route'] ?? null"
                :spark="$stat['spark'] ?? null" />
        @endforeach
    </div>

    {{-- B + C. Activity chart and feature usage -------------------------- --}}
    <div class="grid grid--2-1 u-mt-3">

        <div class="card" id="activityChart">
            <div class="card__head">
                <div>
                    <h2 class="card__title">User Activity</h2>
                    <p class="card__desc">Active users over time.</p>
                </div>

                {{-- The range switch belongs with the chart it drives. --}}
                <div class="segmented" data-segment="#activityChart" role="group" aria-label="Chart range">
                    <button type="button" data-segment-value="daily" aria-pressed="false">Daily</button>
                    <button type="button" data-segment-value="weekly" aria-pressed="false">Weekly</button>
                    <button type="button" data-segment-value="monthly" class="is-active" aria-pressed="true">Monthly</button>
                </div>
            </div>

            <div class="card__body">
                @foreach ($activity as $range => $series)
                    <div data-series="{{ $range }}" class="{{ $range === 'monthly' ? '' : 'u-hide' }}">
                        <x-admin.chart-area
                            :id="'activity-' . $range"
                            :labels="$series['labels']"
                            :values="$series['values']"
                            :height="268">{{ ucfirst($range) }} active users</x-admin.chart-area>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card__head">
                <div>
                    <h2 class="card__title">Feature Usage</h2>
                    <p class="card__desc">Opens in the last 30 days.</p>
                </div>
            </div>
            <div class="card__body">
                <x-admin.chart-bars :data="$featureUsage" />
            </div>
        </div>

    </div>

    {{-- D + E. Ranked tools and most-viewed rates ----------------------- --}}
    <div class="grid grid--2 u-mt-3">

        <div class="card">
            <div class="card__head">
                <div>
                    <h2 class="card__title">Most Used Financial Tools</h2>
                    <p class="card__desc">Share of all calculator runs this month.</p>
                </div>
                <a href="{{ route('backend.tools.emi') }}" class="btn btn--ghost btn--sm">
                    Configure tools <x-admin.icon name="arrow-right" />
                </a>
            </div>
            <div class="card__body">
                <div class="rank-list">
                    @foreach ($topTools as $tool)
                        <div class="rank">
                            <span class="rank__no">{{ $tool['rank'] }}</span>
                            <div>
                                <div class="rank__name">{{ $tool['name'] }}</div>
                                <div class="rank__meta">{{ $tool['uses'] }} runs &middot; {{ $tool['share'] }}% of total</div>
                                <div class="rank__track">
                                    <div class="rank__fill" style="width:{{ $tool['share'] }}%"></div>
                                </div>
                            </div>
                            <x-admin.delta :value="$tool['change']" :direction="$tool['direction']" />
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card__head">
                <div>
                    <h2 class="card__title">Most Viewed Rates</h2>
                    <p class="card__desc">Rate screens opened in the app this month.</p>
                </div>
                <a href="{{ route('backend.market.gold') }}" class="btn btn--ghost btn--sm">
                    Manage rates <x-admin.icon name="arrow-right" />
                </a>
            </div>
            <div class="card__body">
                <div class="rank-list">
                    @foreach ($topRates as $rate)
                        <div class="rank">
                            <span class="rank__no">{{ $loop->iteration }}</span>
                            <div>
                                <div class="rank__name">{{ $rate['name'] }}</div>
                                <div class="rank__meta">{{ $rate['detail'] }} &middot; {{ $rate['views'] }} views</div>
                                <div class="rank__track">
                                    <div class="rank__fill" style="width:{{ $rate['share'] }}%"></div>
                                </div>
                            </div>
                            <x-admin.delta :value="$rate['change']" :direction="$rate['direction']" />
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    {{-- F + G. Recent users and admin activity -------------------------- --}}
    <div class="grid grid--2-1 u-mt-3">

        <div class="card">
            <div class="card__head">
                <div>
                    <h2 class="card__title">Recent Users</h2>
                    <p class="card__desc">Newest registrations on the Bfinz app.</p>
                </div>
                <a href="{{ route('backend.users.index') }}" class="btn btn--ghost btn--sm">
                    All users <x-admin.icon name="arrow-right" />
                </a>
            </div>

            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Registered</th>
                            <th scope="col">Last Active</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentUsers as $user)
                            <tr>
                                <td>
                                    <div class="cell-entity">
                                        <span class="avatar avatar--sm" aria-hidden="true">{{ mb_substr($user['name'], 0, 1) }}</span>
                                        <span class="cell-entity__name">{{ $user['name'] }}</span>
                                    </div>
                                </td>
                                <td class="is-muted">{{ $user['email'] }}</td>
                                <td>{{ $user['registered'] }}</td>
                                <td class="is-muted">{{ $user['last_active'] }}</td>
                                <td><x-admin.status-badge :status="$user['status']" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card__head">
                <div>
                    <h2 class="card__title">Recent Admin Activity</h2>
                    <p class="card__desc">Latest changes made in this panel.</p>
                </div>
            </div>
            <div class="card__body">
                <div class="timeline">
                    @foreach ($adminLog as $entry)
                        <div class="tl">
                            <span class="tl__icon" aria-hidden="true"><x-admin.icon :name="$entry['icon']" /></span>
                            <div>
                                <div class="tl__text"><b>{{ $entry['admin'] }}</b> {{ $entry['action'] }}</div>
                                <div class="tl__meta">{{ $entry['detail'] }} &middot; {{ $entry['module'] }}</div>
                            </div>
                            <span class="tl__time">{{ $entry['time'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="card__foot">
                <a href="{{ route('backend.admin-management.activity-logs') }}" class="btn btn--ghost btn--sm btn--block">
                    View all activity logs
                </a>
            </div>
        </div>

    </div>

    {{-- H. Quick actions -------------------------------------------------- --}}
    <x-admin.section-header
        title="Quick Actions"
        description="Jump straight to the records most often added." />

    <div class="quick">
        @foreach ($quickActions as $action)
            <a href="{{ route($action['route']) }}" class="quick__btn">
                <x-admin.icon :name="$action['icon']" />
                <span>{{ $action['label'] }}</span>
            </a>
        @endforeach
    </div>

@endsection
