# Bfinz — Progress

See [`docs/BUILD_SPEC.md`](BUILD_SPEC.md) for the full spec and its repo-specific amendments.

## Decisions taken

- **2026-09-23** — This repo (`Bfinz-Adminpanel`), previously a training-institute admin panel, is being repurposed **in place** into the Bfinz backend + admin panel. The old education tables/controllers/views are left untouched (not deleted) but are out of scope for Bfinz work.
- **2026-09-23** — No monorepo restructure and no Filament: keep the existing flat Laravel layout, `Backend` controllers + Blade views for admin, and the existing `{status, data, message}` API envelope (`App\Support\ApiResponse`). See the amendment note at the top of `BUILD_SPEC.md`.
- **2026-09-23** — Flutter mobile app is out of scope for this repo (no `app/` folder here); Section 9 of the spec applies to a separate mobile-app project.
- **2026-09-23** — Started with **P0 Foundation** rather than re-doing auth, since OTP + Sanctum login, devices (`user_fcm_tokens`), and login history already existed before this spec (commits `dd25220`, `8cee4ce`).

## Phase status

### P0 Foundation — in progress

Done:
- `states`, `cities`, `banks` migrations + models + factories + seeders (`StatesSeeder`: 36 states/UTs, `CitiesSeeder`: 71 starter cities, `BanksSeeder`: 18 launch banks — trim/extend once §13 Q5 is answered).
- `GET /api/v1/master/states`, `GET /api/v1/master/cities?state_id=`, `GET /api/v1/master/banks?category=` — cached (24h / 24h / 6h), feature-tested.
- `GET /api/v1/app/config` — reads `min_version` / `force_update` / `quick_actions` / `banners` from the existing `Setting` key-value store, cached 10 min, feature-tested.
- Fixed two pre-existing migration bugs that broke `RefreshDatabase`/`migrate:fresh` for the *entire* test suite (found while writing the first tests against a fresh DB):
  - `2026_08_08_000200_cycle_category_and_event_card_tones.php` referenced the now-deleted `App\Models\Event::TONES` constant — inlined the value (`['purple', 'teal', 'green']`, recovered from git history at commit `7f681a2`).
  - `2026_09_22_100500_make_email_nullable_on_users_table.php` used raw MySQL `ALTER TABLE ... CHANGE` syntax, invalid on SQLite (the test driver) — rewritten with `Schema::table(...)->change()`.

Pending:
- `faqs?module=` endpoint (table already exists from the legacy app — needs a `module` column + Bfinz-shaped seed data, or a decision to use a fresh table instead).
- CI pipeline (`.github/workflows/backend.yml`) — none exists yet in this repo.
- Everything else in Section 11 (P1–P10).

### P1 Auth & Profile — done for this pass

Audited the existing OTP/Sanctum login against spec §4.3/§7.2 rather than rebuilding it. Found and fixed:

- **Critical, pre-existing fatal bug**: `App\Http\Requests\BaseFormRequest` (extended by `SendOtpRequest` and `VerifyOtpRequest`) didn't exist anywhere in the codebase — every call to `send-otp`/`verify-otp` was throwing a fatal `Class not found` error. This is presumably why there were zero tests on the auth flow before now. Added the missing base class.
- **Real security/reliability bug**: the resend rate-limiter counted `otp_verifications` rows within a time window, but each successful send deleted the prior row for that mobile before the next check — so the count could never reach the threshold and the limiter never actually engaged. Replaced with Laravel's cache-backed `RateLimiter` (per-mobile and per-IP, independent of the OTP row's lifecycle).
- **Real security bug**: OTPs were stored in plain text (`otp` column). Renamed to `otp_hash`, hashed with `Hash::make`, checked with `Hash::check`.
- Aligned to spec: OTP length 4→6 digits, expiry 10→5 min, mobile validation `digits:10` → `^[6-9]\d{9}$` (rejects numbers that can't be real Indian mobiles), resend cooldown 30s + 5/hour cap per mobile **and** per IP.
- Added the two missing spec-mandated endpoints: `DELETE /auth/profile` (soft delete + anonymise mobile/name/email, revokes all tokens and FCM devices) and `POST /auth/devices` (register/refresh an FCM token outside the login flow). Kept both under the existing `/auth` prefix rather than spec's bare `/profile`/`/devices`, for consistency with this repo's existing route naming.
- Added `POST /auth/resend-otp` as a distinct route (spec has it separate from `send-otp`) — currently identical behaviour to `send-otp`, no `request_id` concept introduced (see deviation note below).
- Added `users.deleted_at` (soft deletes) and `user_fcm_tokens.app_version`/`last_seen_at` columns.
- Found and fixed **two more pre-existing bugs while testing**, both in Carbon 3 (shipped with Laravel 12) vs. code written against Carbon 2 semantics: `diffInSeconds()`/`diffInMinutes()` are signed by default in Carbon 3 (a breaking change), which silently broke the resend-cooldown math. Fixed by passing `absolute: true` explicitly wherever only elapsed time (not direction) matters.
- Wrote the first tests for this flow: `tests/Unit/Services/OtpServiceTest.php` (hashing, expiry, cooldown, hourly caps) and `tests/Feature/Api/AuthOtpTest.php` (validation, full verify lifecycle, lockout, expiry, account deletion, device registration) — 18 tests, all passing.

**Deliberate simplification vs. spec**: no `request_id` round-trip between send/verify (spec's §7.2 table has `send-otp` return a `request_id` that `verify-otp` and `resend-otp` echo back). The current design matches-by-mobile-number instead, which is simpler and was already the existing pattern. Revisit if the mobile app needs to disambiguate concurrent OTP requests for the same number.

**Not done in this pass**: WhatsApp OTP channel (SMS-only currently, via Nettyfish gateway — §13 Q1 still open), `otp_requests.purpose`/`verified_at` fields from the spec's data model (current `otp_verifications` table is login-only, single-purpose).

### P2 Market data — done for this pass (Gold, Silver, Forex, Fuel)

- **Data model**: `metal_rates`, `metal_city_premiums`, `currencies`, `forex_rates`, `fuel_prices`, `sync_runs` — daily granularity (one row per metal/purity/city/day, or currency/day), which is what makes the sync jobs idempotent: a same-day re-run recomputes the row instead of appending a duplicate.
- **Provider layer**: `App\Contracts\Providers\{MetalRateProvider,ForexRateProvider}` interfaces, bound in `AppServiceProvider` from `config('services.*_rate_provider')`.
  - Forex: real adapter, `FrankfurterForexRateProvider` (ECB reference rates via frankfurter.dev — free, no API key). One request fetches EUR→everything, other rates derived by cross-division. Known gap: ECB doesn't publish AED/SAR/QAR (Gulf currencies pegged to USD) — seeded `is_active = false` until a provider that covers them is chosen.
  - Gold/Silver: **placeholder only** — `AdminManualMetalRateProvider` reads today's spot price (INR/troy oz) from a new admin settings page (Settings → Market Rates). Spec §13 Q2 (Metals-API vs. GoldAPI.io) is still open; swapping in a real adapter later is a new class + one config value, nothing else changes.
  - Fuel: **no provider/sync job** — §13 Q3 (source) is still open. `fuel_prices` is read-only from the API side; whatever's in the table (seed data or direct admin entry) is what's served. Wiring a `SyncFuelPrices` job later is additive.
- **Sync jobs**: `SyncMetalRates` (4×/day, 09:00/12:00/15:00/18:00) and `SyncForexRates` (2×/day, 09:15/18:15) scheduled in `routes/console.php`. Both log every run to `sync_runs` (`success`/`failed` + record count), and leave existing data untouched on failure — the API computes a `stale` flag from `fetched_at` age (>24h) rather than the job clearing anything.
- **API**: `/metals/{gold|silver}/{live,details,trend,cities}`, `/forex/{rates,movers,currencies,{code}}`, `/fuel/{prices,trend,cities}` — all read-only from DB, cached where the data doesn't change intra-day. Full request/response shapes in `docs/API.md` and the "Market Data API" sheet of `docs/Bfinz-API-Documentation.xlsx`.
- **Admin**: Settings → Market Rates page (gold/silver spot price entry) — the only admin surface built this pass. Per-city premiums and currency management are DB/seeder-only for now (`MetalCityPremium`, `CurrenciesSeeder`); no admin CRUD UI yet for those or for fuel prices.
- **Real bug found and fixed while testing**: `MetalRate`/`ForexRate`'s `'date'` cast serializes to `'Y-m-d H:i:s'` on write, but `updateOrCreate()`'s array-based search used a bare `'Y-m-d'` string — so the lookup never matched the row just created. Each "idempotent" sync call was silently duplicating data (masked for `metal_rates` because `city_id IS NULL` makes SQL treat every national-rate row as distinct under a unique index, so no constraint violation ever surfaced; it *did* throw a unique-constraint exception for `forex_rates`, which is what surfaced it). Fixed by using `whereDate()` for the lookup instead of relying on `updateOrCreate`'s naive array match, in both sync services.
- Tests: `MetalRateCalculatorTest` (pure formula), `MetalRateSyncServiceTest` + `ForexRateSyncServiceTest` (idempotency, city premiums, change computation, provider-failure handling — all against fake in-test providers, no real HTTP in the suite), `MetalEndpointsTest` + `ForexEndpointsTest` + `FuelEndpointsTest` (API contract, staleness, validation) — 33 new tests, all passing. Full suite: 57/57.

### P3 Alerts & Notifications — done for this pass

- **Data model**: `alerts` (crossing state machine via an `armed` boolean, not just `last_triggered_at` — needed to implement "fire once on crossing, re-arm only after crossing back" per spec §7.6) and `notifications` (soft-deletable).
- **`AlertEvaluationService`**: evaluates one type (`gold`/`silver`/`forex`/`fuel`) at a time against the latest `MetalRate`/`ForexRate`/`FuelPrice` row for the alert's asset/city. `above`/`below` fire once per crossing and re-arm on crossing back; `any_change` fires at most once per calendar day. `fd`/`rd` are valid per the schema but always no-op (those products don't exist until P5) — an alert with no market data yet (e.g. a fuel alert for a city with no price) is silently skipped, not an error.
- **`EvaluateAlerts` job**: dispatched automatically from `SyncMetalRates` (`gold` + `silver`) and `SyncForexRates` (`forex`) on successful sync, per spec §6 — no manual trigger endpoint.
- **Push notifications**: `App\Contracts\Providers\PushSender` interface, bound to a `LogPushSender` placeholder (writes to the log instead of calling Firebase — no FCM project/credentials yet, spec §13 Q10). Swapping in a real `FcmPushSender` later is one class + a config value, same pattern as the metal-rate/forex providers.
- **API**: `/alerts` CRUD (max 20 active per user, ownership-enforced) and `/notifications` (list with tabs + `unread_count`, mark read/read-all, delete/delete-all).
- **Real naming collision found and fixed**: this app's `notifications` table (spec §5 schema: `user_id`, `category`, `title`, `body`, `data`, `read_at`) has the same name Laravel's built-in `Illuminate\Notifications\Notifiable` trait expects for its own (differently-shaped) notifications table. The `User` model had `use Notifiable` from the framework scaffold, unused anywhere (`grep`-confirmed no `->notify()` calls in the app) — removed it, since keeping both would silently corrupt whichever system wrote to the table last.
- **Deliberate simplification vs. spec**: a "broadcast" notification (`user_id = null`, meant to be visible to every user) has no per-user read/delete state in this pass — that needs a pivot table (`notification_id`, `user_id`, `read_at`), which isn't worth building until the admin "send broadcast" feature itself is built (P8, `Broadcast notifications` admin resource). For now, broadcasts are listed and always counted as unread, but `PATCH .../read` and `DELETE` on one return 403. Alert-triggered notifications (the only kind this app currently creates) always have a real `user_id`, so this gap doesn't affect them.
- Tests: `AlertEvaluationServiceTest` (crossing/re-arm/any-change/inactive/fd-rd-skip/push-delivery), `AlertEndpointsTest`, `NotificationEndpointsTest` — 18 new tests, all passing.

### P4 Home & Search — done for this pass

- **`GET /home?city_id=`**: one call renders the whole Home screen — gold/silver/USD-INR/top-4-popular-forex from P2's market data, `quick_actions` from the existing `Setting` store. Public but personalised (`greeting_name`, `location`) when a bearer token is present — deliberately **not** behind `auth:sanctum` middleware (which would 401 a guest); uses `$request->user('sanctum')` to resolve the user ad hoc without requiring a token. The public portion is cached 5 min per `city_id`; personalised fields are computed fresh every request so the cache can't leak one user's name to another.
- **`best_fd` and `loan_banners` are always empty arrays** — FD/RD and Loans (P5) don't exist in this repo yet. Wiring them in later is additive (fill in the two array-building calls in `HomeController::marketSection()`).
- **`search_history` table + `GET/DELETE /search/recent`, `GET /search?q=`, `GET /search/suggestions`**: search only groups over what actually exists as a module — `banks` (P0) and `currencies` (P2). The spec's other groups (`tools`, `ifsc`, `loans`, `deposits`, `articles`) aren't stubbed with fake data; they simply aren't in the response yet, to be added as those modules land (P5 loans/deposits, P7 tools, P8 articles, P9 ifsc). `suggestions` returns a static placeholder list for the same reason.
- Tests: `HomeEndpointTest`, `SearchEndpointsTest` — 11 new tests, all passing.
- **Same Eloquent-pluralization bug hit a third time**: `SearchHistory` → default table guess `search_histories`, but spec/migration name it `search_history` (singular). Same fix pattern as `MetalCityPremium` in P2 — explicit `protected $table = 'search_history'`. Worth remembering: any model name that doesn't pluralize to exactly the migration's table name needs this.

**Full suite status after P0–P4**: 86/86 tests passing, Pint clean.

**Documentation**: also found and read `docs/ADMIN-PANEL-SPEC.md` — this is a **legacy spec for the old training-institute product** ("HireMinds Academy"), unrelated to Bfinz; no action needed beyond noting it (already covered by `CLAUDE.md`'s "legacy tables are out of scope" note). Updated `docs/Bfinz-API-Documentation.xlsx` (pre-existing, hand-maintained endpoint sheet) to fix stale info from before the P1 fixes (4→6 digit OTP, old rate-limit wording) and added a sheet per phase (P1's 3 new endpoints appended to "OTP Login API"; "Master & Config API" for P0; "Market Data API" for P2; "Alerts & Notifications API" for P3; "Home & Search API" for P4), in its existing format. Created `docs/API.md` as the git-diffable mirror of that spreadsheet (per spec §2, which already names `docs/API.md` as the maintained endpoint reference) — keep both in sync going forward when an endpoint changes.

## Open questions (blocking, from spec §13 — need Pavi's answers)

1. OTP channel: WhatsApp (Meta Cloud API / Gupshup) or SMS provider (MSG91 / 2Factor)? Which account/keys?
2. Gold provider: Metals-API or GoldAPI.io (plan tier)? City premium values for launch cities?
3. Fuel price source: admin CSV upload only, or approve a scraper?
4. Loan leads: partner banks/NBFCs to send leads to, or store only?
5. Launch coverage: which banks (top 15–20?) and which states/cities for rates at launch? (`BanksSeeder` currently seeds 18 major banks as a placeholder.)
6. SGB: keep "Retirement – SGB" or replace with Gold ETF / digital gold info?
7. CPI: confirm current MoSPI base year to display (Figma shows 2012 = 100).
8. Profile tab: screens not in Figma yet — build a minimal version now?
9. Tax: which assessment years to support at launch, and who confirms slab data?
10. Hosting: server/provider for API, Redis, backups; API domain?

## Known issues

- None currently open beyond the two migration bugs already fixed above.
