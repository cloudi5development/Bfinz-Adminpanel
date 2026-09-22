{{--
    Bfinz admin dashboard.

    Seven blocks, in the order an operator actually scans them: the headline
    counters, the statistic split and market trend, then the ranked lists, the
    recent records, and the shortcuts.

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

    {{-- B. Current statistic + market overview -------------------------- --}}
    <div class="stat-row u-mt-3">

        <div class="card">
            <div class="card__head">
                <h2 class="card__title">Current Statistic</h2>
            </div>

            <div class="card__body">
                <x-admin.chart-arcs :data="$statistic" />

                <div class="legend">
                    @foreach ($statistic as $item)
                        <div class="legend__row">
                            <span class="legend__dot" style="background:{{ $item['colour'] }}" aria-hidden="true"></span>
                            <span class="legend__label">
                                {{ $item['label'] }}
                                <span class="legend__pct">({{ $item['percent'] }}%)</span>
                            </span>
                            <span class="legend__value">{{ $item['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card__head">
                <div>
                    <h2 class="card__title">Market Overview</h2>
                    <p class="card__desc">Weekly views across the market rate screens.</p>
                </div>

                <div class="u-row u-row--sm u-wrap">
                    <div class="series-toggles" role="group" aria-label="Series shown">
                        @foreach ($market['series'] as $s)
                            <label class="series-toggle {{ $s['on'] ? 'is-on' : '' }}"
                                   style="--series: {{ $s['colour'] }}">
                                <input type="checkbox" @checked($s['on'])>
                                <span class="series-toggle__mark" aria-hidden="true"></span>
                                <span>{{ $s['name'] }}</span>
                            </label>
                        @endforeach
                    </div>

                    <label class="visually-hidden" for="marketPeriod">Reporting period</label>
                    <select class="select select--pill" id="marketPeriod">
                        <option>Weekly (2026)</option>
                        <option>Monthly (2026)</option>
                        <option>Yearly</option>
                    </select>
                </div>
            </div>

            <div class="card__body">
                {{-- Scrolls on narrow screens. The SVG scales uniformly to keep
                     its labels undistorted, which means they also shrink with
                     the container — below ~1100px a min-width plus scroll keeps
                     them readable instead of microscopic. --}}
                <div class="chart-scroll">
                    <x-admin.chart-lines
                        :labels="$market['labels']"
                        :series="$market['series']"
                        :highlight="$market['highlight']" />
                </div>
            </div>
        </div>

    </div>

    {{-- C + D. Ranked tools and most-viewed rates ----------------------- --}}
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

    {{-- E + F. Recent users and admin activity -------------------------- --}}
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

    {{-- G. Quick actions -------------------------------------------------- --}}
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
