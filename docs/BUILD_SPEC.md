# BFINZ — Build Specification for Claude Code

> Put this file in the repo root as `CLAUDE.md` (or `docs/BUILD_SPEC.md` and reference it from `CLAUDE.md`).
> Owner: Pavithran G (Cloudi5 Technologies). Spec version: 1.0 — 23 Sep 2026.

> **Repo-specific amendments agreed 2026-09-23 (see `docs/PROGRESS.md` for the decision log) — these override the sections below where they conflict:**
> - **No monorepo restructure.** This repo (`Bfinz-Adminpanel`) already existed as a flat Laravel app (previously an education/training-institute admin panel) and is being **repurposed in place** into Bfinz. There is no `backend/` subfolder — the Laravel app stays at repo root.
> - **No Filament.** The existing hand-built `Backend` controllers + Blade admin views (routes/admin.php) are the admin panel convention here; new admin CRUD follows that pattern instead of Section 8's Filament resources.
> - **Backend + admin only in this repo.** There is no Flutter `app/` folder here; Section 9 (Flutter architecture) and the Flutter parts of Section 10.2 apply to a separate mobile-app repo, not this one.
> - **Existing envelope kept.** The API already uses `{ status: bool, data, message }` (see `App\Support\ApiResponse` / `App\Traits\ApiResponser`), not the `{ success, data, meta, message }` shape in §4.1. New endpoints use the existing envelope for consistency; `meta` fields (e.g. `updated_at`, `source`, `cache_ttl`, `stale`) are added as extra top-level keys via the existing `extra` param rather than nested under `meta`.
> - **Auth already partly built**: OTP + Sanctum login, `devices` (as `user_fcm_tokens`), `login_histories` exist already (`App\Http\Controllers\Api\V1\AuthController`). Treat §7.2 as a gap-check against that code, not a rebuild.
> - **Two pre-existing migration bugs were fixed** while building P0 because they broke `RefreshDatabase`/`migrate:fresh` for every future test: a migration referencing the deleted `App\Models\Event` class, and a migration using raw MySQL `ALTER TABLE ... CHANGE` syntax that isn't valid on SQLite (the test driver).

---

## 0. How Claude Code must work on this project

1. Read this whole file before writing any code. Re-read the relevant module section before starting that module.
2. Work **one phase at a time** (Section 11). Do not start the next phase until the current phase's acceptance criteria and tests pass.
3. **Test-first for logic**: write the failing test, then the code. Every endpoint gets a feature test; every calculator/service gets unit tests.
4. After each task, run the full test suite (`php artisan test` / `flutter test`) and linters. Never mark a task done with failing, skipped or commented-out tests.
5. Keep `docs/PROGRESS.md` updated: phase, tasks done, tasks pending, known issues, decisions taken.
6. Never commit secrets. All keys go in `.env` / `--dart-define`; add every new key to `.env.example`.
7. External providers are always called through an interface + adapter, and **always faked in tests** (`Http::fake()`). No test may hit the real internet.
8. When the spec is ambiguous or conflicts with Figma, **stop and ask** — list the question in `docs/PROGRESS.md` under "Open questions" and pick the safest default only if told to continue.
9. Small, focused commits using Conventional Commits (`feat(gold): add trend endpoint`).
10. Use the Figma MCP (file key below) to read exact labels, colors and spacing when building Flutter screens. Do not guess UI text.

---

## 1. Product summary

Bfinz is a **free** Indian personal-finance information app. Users see live-ish market rates (gold, silver, forex, fuel), compare bank products (FD, RD, savings, loans), plan goals (marriage, home, retirement), use 12+ calculators, and access RBI updates, fraud-safety content, bank holidays, IFSC/MICR, ATM/branch locator, banking forms and income-tax tools. Users get price/rate alerts via push notifications.

No money is moved in the app. It is an information + comparison + planning app.

---

## 2. Tech stack & repository layout

| Layer | Choice |
|---|---|
| Backend | Latest stable Laravel, PHP 8.3+, MySQL 8, Redis (cache + queues) |
| Auth | Laravel Sanctum (bearer tokens), mobile OTP login |
| OTP delivery | Pluggable `OtpSender` interface — WhatsApp (Meta Cloud API / Gupshup) primary, SMS fallback |
| Admin panel | Filament (latest stable) inside the Laravel app, at `/admin` |
| Jobs | Laravel Scheduler + queued jobs (Redis), Horizon for monitoring |
| Push | Firebase Cloud Messaging (HTTP v1) |
| Mobile | Flutter (latest stable), GetX (state, routing, DI), Dio (HTTP), Firebase Messaging |
| Backend tests | Pest (PHPUnit), Larastan level 6+, Laravel Pint |
| App tests | `flutter_test`, `mocktail`, `integration_test`, `flutter analyze` (zero warnings) |
| CI | GitHub Actions — backend and app pipelines |

```
bfinz/
├── backend/                 # Laravel API + Filament admin
├── app/                     # Flutter app
├── docs/
│   ├── BUILD_SPEC.md        # this file
│   ├── PROGRESS.md          # maintained by Claude Code
│   ├── API.md               # generated/maintained endpoint reference
│   └── openapi.yaml         # OpenAPI 3.1 contract (source of truth for app ↔ API)
└── .github/workflows/
```

---

## 3. Figma reference

- File: `https://www.figma.com/design/XQdIRpEMtUrLMIKpYbjI2M/Bfinz---UI-2026-`
- File key: `XQdIRpEMtUrLMIKpYbjI2M`, page `Design` (`0:1`)

| Section | Node ID | Section | Node ID |
|---|---|---|---|
| Log In | 1:58 | Goal | 1:474935 |
| Home | 1:9807 | RBI | 5:3427 |
| Search | 1:10805 | Loan Comparison | 6:16411 |
| Notification | 1:17213 | Financial Information & Compliance Corner | 8:3975 |
| Gold Rate | 1:24440 | Finance Tools | 8:93026 |
| Silver Rate | 1:120510 | Cyber Crime Help Center | 8:219165 |
| Currency Details | 1:165501 | Banking Utilities | 8:263470 |
| Fuel Price (A) | 1:207414 | IFSC & MICR | 8:285859 |
| Fuel Price (B) | 1:248937 | ATM & Branch Locator | 8:301784 |
| FD Rates | 1:290493 | Banking Forms & Cheque Guidance | 8:327824 |
| RD Rates & Bank Detail | 1:347608 | Income Tax Slabs | 8:359163 |
| Savings Account | 1:398394 | Tax Calculator | 8:368489 |

Bottom navigation: **Market · Loans · Banks · Profile**.

**Known design issues — do not copy blindly:**
- Many frames contain leftover layers from another project ("Buy Again", "chotekisan", "Fresh", "SPECIAL"). Ignore them.
- Two "Fuel Price" sections exist — treat as one module (Petrol / Diesel / CNG tabs).
- Profile tab has no screens yet → build a minimal Profile (Section 7.2) and flag in PROGRESS.md for design.
- "Live" labels on gold/forex: data is refreshed a few times a day, so the UI must show **"Updated: <time> IST"** from the API's `updated_at`.
- Sample numbers in Figma (e.g. EMI ₹13,702) are placeholders. Always compute from formulas; never hardcode design values.

---

## 4. Engineering rules (backend)

### 4.1 API conventions
- Base path `/api/v1`. JSON only. Dates ISO-8601 with `+05:30`. Money as **integer paise** in DB, returned as numbers in rupees with 2 decimals.
- Standard envelope:
```json
{ "success": true, "data": { }, "meta": { "updated_at": "2026-09-23T09:30:00+05:30", "source": "metals_api", "cache_ttl": 1800 }, "message": null }
```
- Errors:
```json
{ "success": false, "error": { "code": "VALIDATION_ERROR", "message": "…", "fields": { "mobile": ["…"] } } }
```
  Codes: `VALIDATION_ERROR` 422, `UNAUTHENTICATED` 401, `FORBIDDEN` 403, `NOT_FOUND` 404, `RATE_LIMITED` 429, `UPSTREAM_UNAVAILABLE` 503, `SERVER_ERROR` 500.
- Pagination: `?page=&per_page=` (max 50), `meta.pagination {page, per_page, total, last_page}`.
- Use API Resources for every response; Form Requests for every input.
- Public endpoints (rates, content, masters): no auth, cached in Redis, `Cache-Control` headers. User endpoints: `auth:sanctum`.

### 4.2 Architecture
- Controllers thin → Services (business logic) → Repositories/Eloquent.
- Every external source behind an interface in `app/Contracts/Providers/*` with adapters in `app/Providers/Data/*`. Bind in a service provider; choose adapter from config.
- Sync jobs write to DB; **the API never calls an external provider during a user request** (exception: ATM locator, which is proxied and cached per geo-cell).
- If a sync fails, keep serving last good data and set `meta.stale = true` when older than the module's max age.
- Every admin-editable dataset has `source`, `updated_by`, `updated_at`; log changes in `audit_logs`.

### 4.3 Security
- OTP: 6 digits, hashed (never stored plain), 5-minute expiry, max 5 verify attempts, resend after 30s, max 5 sends/hour per mobile and per IP.
- Rate limit: 60 req/min per user/IP on public endpoints, stricter on auth.
- Validate Indian mobile numbers (`^[6-9]\d{9}$`).
- Loan eligibility/leads collect personal data → explicit consent checkbox stored with timestamp (DPDP Act). Encrypt PAN if ever collected (`encrypted` cast). Do not collect Aadhaar numbers.
- Admin panel: separate `admins` guard, 2FA, roles (super_admin, content_editor, data_editor, support) via policies.

---

## 5. Data model (MySQL)

Create migrations, models, factories and seeders for all. Indexes on every foreign key and every filter column.

**Core / masters**
- `users` (id, name, mobile unique, state_id, city_id, last_login_at, timestamps, soft deletes)
- `otp_requests` (mobile, otp_hash, purpose, attempts, expires_at, verified_at, ip)
- `devices` (user_id, fcm_token unique, platform, app_version, last_seen_at)
- `states` (id, name, code), `cities` (id, state_id, name, lat, lng)
- `banks` (id, name, short_name, category enum[public,private,sfb,nbfc,foreign,cooperative], logo_url, rating, website, is_active)
- `faqs` (module, question, answer, sort_order)
- `app_settings` (key, value json)

**Market data**
- `metal_rates` (metal enum[gold,silver], purity enum[24k,22k,18k,999], city_id nullable, rate_per_gram_paise, change_paise, change_pct, rate_date, fetched_at, source) — unique (metal, purity, city_id, rate_date, fetched_at)
- `metal_city_premiums` (city_id, metal, premium_paise) — admin-managed
- `forex_rates` (base, quote='INR', rate decimal(18,6), change, change_pct, rate_date, source) — unique (base, rate_date)
- `currencies` (code, name, country, flag, group enum[popular,asia,middle_east,europe,americas,other], is_active)
- `fuel_prices` (fuel enum[petrol,diesel,cng], city_id, price_paise, change_paise, price_date, source)

**Banking products (admin-managed)**
- `deposit_rates` (bank_id, product enum[fd,rd], tenure_min_days, tenure_max_days, rate_general, rate_senior, min_amount_paise, effective_from, is_best)
- `savings_accounts` (bank_id, account_type, interest_rate, min_balance_paise, features json, charges json, state_id nullable)
- `loan_products` (bank_id, loan_type enum[personal,home,vehicle,gold,medical,marriage,education,agriculture], rate_min, rate_max, amount_min, amount_max, tenure_min_months, tenure_max_months, processing_fee, foreclosure_charges, prepayment_charges, late_payment_charges, bounce_charges, stamp_duty, documents json, eligibility json {age_min, age_max, min_income, min_cibil, employment_types[]}, features json, process_steps json, is_active)
- `loan_leads` (user_id, loan_product_id, inputs json, consent_at, status)
- `loan_partners` (name, logo_url, sort_order)

**Goals & investments**
- `goals` (user_id, type enum[marriage,home,retirement,custom], title, target_amount_paise, timeline_years, monthly_income_paise, existing_savings_paise, status)
- `goal_plans` (goal_id, plan_type enum[fd,rd,mf_sip,mf_lumpsum,ppf,gold,govt_scheme,bank_deposit], instrument_ref, expected_return, monthly_required_paise, summary json, selected bool)
- `mutual_funds` (scheme_code unique, name, amc, category, sub_category, nav decimal(12,4), nav_date, returns json {1y,3y,5y})
- `govt_schemes` (code [ppf,ssy,nsc,scss,kvp,…], name, rate, lock_in, min_amount, effective_from)

**User data**
- `alerts` (user_id, type enum[gold,silver,forex,fuel,fd,rd], asset, city_id nullable, condition enum[above,below,any_change], target_value, is_active, last_triggered_at)
- `notifications` (user_id nullable = broadcast, category enum[alerts,banking,loans,general], title, body, data json, read_at, deleted_at)
- `saved_calculations` (user_id, calculator, inputs json, result json, goal_id nullable)
- `networth_items` (user_id, kind enum[asset,liability], category, name, amount_paise)
- `search_history` (user_id, query, created_at)

**Content & utilities**
- `articles` (section enum[safety,literacy,ombudsman,cyber_help,cheque,rbi_rules], category, title, slug, summary, body (markdown), video_url, sort_order, is_published)
- `rbi_updates` (category, title, summary, body, source_url, published_at)
- `rbi_key_rates` (name [repo,reverse_repo,crr,slr,msf,bank_rate], value, effective_from)
- `rbi_downloads` (year, title, file_url, category)
- `videos` (category, youtube_id, title, sort_order)
- `bank_directory` (bank_id, level, designation, name, email, phone, address)
- `registered_lenders` (name, normalized_name, reg_no, type, status) — from RBI NBFC list
- `economic_indicators` (indicator [cpi,food_inflation,…], period (YYYY-MM), value, yoy_pct, base_year, source)
- `bank_holidays` (date, state_id nullable = national, name, type enum[national,regional,weekend_rule], bank_scope enum[all,…])
- `ifsc_branches` (ifsc unique, micr, bank_name, bank_code, branch, address, city, district, state, contact, upi, neft, rtgs, imps, lat, lng)
- `banking_forms` (bank_id, type enum[banking,loan], title, file_url)
- `tax_slabs` (assessment_year, regime enum[new,old], age_category enum[below_60,60_80,above_80], from_amount, to_amount nullable, rate_pct) + `tax_rules` (assessment_year, regime, standard_deduction, rebate_limit, rebate_max, cess_pct, surcharge json)
- `audit_logs`, `sync_runs` (job, status, records, error, started_at, finished_at)

---

## 6. External data & sync jobs

| Module | Provider (primary → fallback) | Job | Schedule |
|---|---|---|---|
| Gold / Silver | Metals-API or GoldAPI.io (spot in INR) → admin manual | `SyncMetalRates` | 4×/day 09:00, 12:00, 15:00, 18:00 IST |
| Forex | Frankfurter (ECB) + ExchangeRate-API open access for currencies ECB lacks (SAR, QAR, AED…) | `SyncForexRates` | 2×/day |
| Fuel | Admin upload (CSV) / scraper adapter for OMC city prices | `SyncFuelPrices` | daily 06:30 |
| Mutual funds | AMFI `NAVAll.txt` | `SyncMutualFundNav` | daily 23:00 |
| CPI & indicators | MoSPI (eSankhyiki) → admin entry | `SyncEconomicIndicators` | daily check, monthly data |
| RBI updates | RBI RSS feeds (press releases, notifications) | `SyncRbiUpdates` | every 3 h |
| IFSC / MICR | Razorpay IFSC dataset (GitHub release) | `ImportIfscDataset` (artisan command) | monthly |
| Branch geocode | OSM Nominatim (respect 1 req/s) | `GeocodeBranches` | after import, batched |
| ATMs | OSM Overpass (live, cached per ~1 km grid, 24 h) → Google Places (paid, config flag) | on request | — |
| Registered lenders | RBI NBFC list (import file) | `ImportRegisteredLenders` | monthly |
| FD/RD/Savings/Loans, holidays, tax slabs, directory, content, forms | Admin panel (Filament) | — | manual |

**Gold/silver derivation:** `rate_24k_per_gram = spot_XAU_INR_per_oz / 31.1035 + city_premium`; 22k = 24k × 0.916; 18k = 24k × 0.75. Keep the formula in one service with unit tests; admin can override any day's rate.

After every market sync → dispatch `EvaluateAlerts(type)`.

---
## 7. API contract by module

Legend: 🔓 public (cached) · 🔒 `auth:sanctum`. TTL = Redis cache time. Every endpoint must appear in `docs/openapi.yaml` with request/response schemas.

### 7.1 Masters & config 🔓
| Method | Path | Notes | TTL |
|---|---|---|---|
| GET | `/master/states` | | 24 h |
| GET | `/master/cities?state_id=` | | 24 h |
| GET | `/master/banks?category=` | | 6 h |
| GET | `/faqs?module=` | module: fd, rd, gold, silver, forex, fuel, cpi, loans… | 6 h |
| GET | `/app/config` | min_version, force_update, quick_actions[], banners[], support links | 10 min |

### 7.2 Auth & Profile
| Method | Path | Body / Query | Returns |
|---|---|---|---|
| POST | `/auth/send-otp` 🔓 | mobile | request_id, resend_after (s) |
| POST | `/auth/resend-otp` 🔓 | mobile, request_id | same |
| POST | `/auth/verify-otp` 🔓 | mobile, otp, request_id, device {fcm_token, platform, app_version} | token, user, is_new_user |
| POST | `/auth/logout` 🔒 | — | revokes current token, removes device |
| GET | `/profile` 🔒 | — | user |
| PUT | `/profile` 🔒 | name, state_id, city_id | user |
| DELETE | `/profile` 🔒 | — | account deletion (soft delete + anonymise; needed for store policies) |
| POST | `/devices` 🔒 | fcm_token, platform, app_version | upsert |

### 7.3 Home 🔓 (personalised fields when token present)
`GET /home?city_id=` → `{ greeting_name, location, gold {24k,22k,change,updated_at}, silver {…}, usd_inr {…}, forex_top[4], best_fd[3], loan_banners[], quick_actions[] }` — TTL 5 min (per city). One call must render the whole Home screen.

### 7.4 Search
| Method | Path | Notes |
|---|---|---|
| GET 🔓 | `/search?q=` | Groups: tools, banks, ifsc, loans, deposits, articles. Min 2 chars. Stores history if authed |
| GET 🔒 | `/search/recent` | last 10 |
| DELETE 🔒 | `/search/recent` | clear |
| GET 🔓 | `/search/suggestions` | quick tools + categories |

### 7.5 Notifications 🔒
| Method | Path | Notes |
|---|---|---|
| GET | `/notifications?tab=all\|alerts\|banking\|loans` | paginated, includes broadcasts; `unread_count` in meta |
| PATCH | `/notifications/{id}/read` | |
| POST | `/notifications/read-all` | |
| DELETE | `/notifications/{id}` | |
| DELETE | `/notifications` | clear all for user |

### 7.6 Alerts 🔒 (shared by gold, silver, forex, fuel, fd, rd)
| Method | Path | Body |
|---|---|---|
| GET | `/alerts?type=` | |
| POST | `/alerts` | type, asset (e.g. `24k`, `USD`, `petrol`, `bank:12:12m`), city_id?, condition, target_value? |
| PUT | `/alerts/{id}` | same fields + is_active |
| DELETE | `/alerts/{id}` | |

Rules: max 20 active alerts/user; `any_change` fires at most once per day per alert; `above`/`below` fire on crossing (previous value on the other side), then re-arm only after crossing back.

### 7.7 Gold & Silver 🔓 (`{metal}` = gold | silver)
| Method | Path | Returns | TTL |
|---|---|---|---|
| GET | `/metals/{metal}/live?city_id=` | purities[] {purity, per_gram, per_8g, per_10g, change, change_pct}, updated_at | 10 min |
| GET | `/metals/{metal}/details?city_id=` | market overview, 7-day high/low with dates, today's range | 10 min |
| GET | `/metals/{metal}/trend?city_id=&purity=&range=7d\|1m\|6m\|1y` | points[] {date, rate} | 30 min |
| GET | `/metals/{metal}/cities?state_id=` | cities with today's rate (Filter Rates / Select State) | 30 min |

Calculator (client): `value = weight_g × rate_per_gram + making_charges (% or flat) ; GST 3% on (value + making)`.

### 7.8 Forex 🔓
| Method | Path | Returns | TTL |
|---|---|---|---|
| GET | `/forex/rates?group=all\|popular\|asia\|middle_east\|europe` | pair, rate, change, change_pct | 10 min |
| GET | `/forex/{code}?range=7d\|1m\|6m\|1y` | detail + history points | 30 min |
| GET | `/forex/movers` | top_gainers[5], top_losers[5] | 10 min |
| GET | `/forex/currencies` | code, name, country, flag, group | 24 h |

Converter is client-side using `/forex/rates`; cross rates via INR.

### 7.9 Fuel 🔓
| Method | Path | TTL |
|---|---|---|
| GET | `/fuel/prices?fuel=petrol\|diesel\|cng&state_id=&city_id=` | 30 min |
| GET | `/fuel/trend?fuel=&city_id=&range=` | 30 min |
| GET | `/fuel/cities?state_id=&fuel=` | 30 min |

### 7.10 FD & RD 🔓 (`{product}` = fd | rd)
| Method | Path | Notes |
|---|---|---|
| GET | `/deposits/{product}?category=&tenure_months=&senior=0\|1&sort=rate_desc&page=` | list with best-rate flag |
| GET | `/deposits/{product}/insights` | highest rate (bank), highest senior rate, average across N banks |
| GET | `/deposits/{product}/banks/{bank_id}` | tenure slabs, general & senior rates, min amount, bank info |
| POST | `/deposits/{product}/compare` | bank_ids[2..4], tenure_months, amount → per-bank rate + maturity (server uses same formula as app) |

### 7.11 Savings accounts 🔓
| GET | `/savings-accounts?account_type=&state_id=&city_id=&sort=` |
|---|---|
| GET | `/savings-accounts/{id}` |
| POST | `/savings-accounts/compare` (ids[2..4]) |

### 7.12 Loans
| Method | Path | Notes |
|---|---|---|
| GET 🔓 | `/loans/types` | with icons & short copy |
| GET 🔓 | `/loans?type=&amount=&tenure_months=&sort=` | each item includes computed EMI for the given amount/tenure |
| GET 🔓 | `/loans/{id}` | full detail: overview, charges, documents, eligibility, features, digital banking, process steps |
| POST 🔓 | `/loans/compare` | ids[2..4], amount, tenure_months |
| POST 🔓 | `/loans/eligibility` | loan_type, age, monthly_income, employment_type, cibil_score?, amount, tenure_months → eligible[] + reasons for ineligible (rule engine on `eligibility` json + FOIR ≤ 50% using existing_emi) |
| POST 🔒 | `/loans/leads` | loan_product_id, inputs, consent=true (required) |
| GET 🔓 | `/loans/partners` | |

### 7.13 Goals & Investments 🔒 (lists of instruments are 🔓)
| Method | Path | Notes |
|---|---|---|
| GET 🔓 | `/goals/types` | marriage, home, retirement (+ copy, icons) |
| GET / POST | `/goals` | create: type, target_amount, timeline_years, monthly_income, existing_savings |
| GET / PUT / DELETE | `/goals/{id}` | |
| GET | `/goals/{id}/recommendations` | plan types by horizon: ≤2y → bank deposits (FD/RD/savings); 2–5y → FD, RD, debt/liquid & large-cap MF; >5y (retirement) → PPF, govt schemes, equity MF, gold. Each with expected return range and **monthly amount required** |
| POST | `/goals/{id}/plans` | plan_type, instrument_ref → saves goal_plan, returns summary |
| GET | `/goals/{id}/summary` | target, timeline, selected plan(s), monthly required, projected corpus, shortfall |
| GET 🔓 | `/investments/types` | bank deposits, mutual funds, govt savings, gold |
| GET 🔓 | `/investments/mutual-funds?category=&amc=&q=&page=` | |
| GET 🔓 | `/investments/mutual-funds/{scheme_code}` | NAV, returns |
| GET 🔓 | `/investments/govt-schemes` | PPF etc. with current rates |
| POST 🔓 | `/investments/compare` | refs[2..4], amount, years |

Monthly required (future value of annuity-due): `M = FV_needed × i / (((1+i)^n − 1) × (1+i))`, `FV_needed = target − existing_savings × (1+i)^n`, `i = annual_rate/12`, `n = years×12`.

### 7.14 RBI 🔓
| GET | `/rbi/updates?category=repo\|crr_slr\|credit_card\|loan\|digital_lending&page=` |
|---|---|
| GET | `/rbi/updates/{id}` |
| GET | `/rbi/rules?category=banking\|upi\|credit_card\|digital_lending\|customer_rights\|kyc` |
| GET | `/rbi/key-rates` |
| GET | `/rbi/downloads?year=&category=` |

### 7.15 Financial Information & Compliance Corner 🔓
| GET | `/articles?section=safety\|literacy\|ombudsman\|cyber_help\|cheque&category=` |
|---|---|
| GET | `/articles/{slug}` |
| GET | `/videos?category=` |
| GET | `/bank-directory?q=` · `/bank-directory/{bank_id}` (escalation matrix levels) |
| GET | `/lenders/verify?name=` → `{ status: registered\|not_found\|cancelled, matches[] }` (fuzzy match on normalized name; always show "verify on RBI site" disclaimer) |
| GET | `/economy/cpi?year=&state_id=` → value, yoy, base_year, period, monthly series |
| GET | `/economy/indicators` → food inflation, household costs, savings rate, … |

### 7.16 Finance Tools
All calculators run in Flutter (pure Dart, Section 9). Server:
| Method | Path |
|---|---|
| GET 🔓 | `/config/calculators` (default inflation %, default returns, GST rates) |
| GET / POST 🔒 | `/calculations` (calculator, inputs, result, goal_id?) |
| DELETE 🔒 | `/calculations/{id}` |
| GET / POST 🔒 | `/networth/items` · PUT/DELETE `/networth/items/{id}` · GET `/networth/summary` |

### 7.17 Cyber Crime Help Center 🔓
Content from `/articles?section=cyber_help`. Helpline 1930 and `cybercrime.gov.in` are links/intent actions. `POST /complaint-letter` (🔒) → returns a generated PDF of the bank complaint letter from user inputs (bank, account last 4 digits, date, amount, description). No tracking API exists — "Track complaint" opens the portal.

### 7.18 Banking Utilities, IFSC, Locator 🔓
| GET | `/bank-holidays?state_id=&bank_id=&year=&month=` (national + state holidays + 2nd/4th Saturday + Sundays) |
|---|---|
| GET | `/bank-holidays/next?state_id=` |
| GET | `/ifsc/banks?q=` → `/ifsc/states?bank=` → `/ifsc/cities?bank=&state=` → `/ifsc/branches?bank=&state=&city=&q=` |
| GET | `/ifsc/{ifsc}` (also accepts MICR via `/micr/{micr}`) |
| GET | `/locator/atms?lat=&lng=&bank=&radius_m=2000` → name, bank, distance_m, address, hours, lat, lng |
| GET | `/locator/branches?lat=&lng=&bank=&radius_m=5000` |

### 7.19 Banking Forms & Cheque Guidance 🔓
`GET /forms?bank_id=&type=banking|loan` · cheque content via `/articles?section=cheque`. Amount-in-words (Indian system: lakh/crore) is client-side.

### 7.20 Income Tax 🔓
| GET | `/tax/slabs?assessment_year=&regime=new\|old&age_category=` |
|---|---|
| GET | `/tax/assessment-years` |
| POST | `/tax/calculate` → body: assessment_year, regime, age_category, income heads, deductions (old regime: 80C, 80D, HRA, home-loan interest, …) → tax by slab, rebate, cess, total, effective rate, and comparison new vs old |

Tax rules live in DB (`tax_slabs`, `tax_rules`), never hardcoded, so budget changes need no app release.

---

## 8. Admin panel (Filament)

Resources (CRUD + import/export CSV where marked ⇅):
Dashboard (users, DAU, alerts fired today, **sync health per job**), Users, Admins & Roles, Banks ⇅, States/Cities ⇅, Metal rates (view + override) & City premiums ⇅, Forex (view), Fuel prices ⇅, Deposit rates ⇅, Savings accounts ⇅, Loan products ⇅, Loan leads (export), Loan partners, Mutual funds (view), Govt schemes, Articles (markdown editor), Videos, RBI updates / key rates / downloads, Bank directory ⇅, Registered lenders ⇅, Economic indicators ⇅, Bank holidays ⇅, Banking forms, Tax slabs & rules, FAQs, App settings & banners, Broadcast notifications (target: all / state / city), Sync runs (logs, "run now" button), Audit log.

Every data-editor save writes `audit_logs`. Every "rates" resource shows `source` and `updated_at`.

> Repo amendment: build these as `Backend` controllers + Blade views (existing convention), not Filament resources — see the amendment note at the top of this file.

---

## 9. Flutter app architecture

*(Out of scope for this repo — see the amendment note at the top of this file. Kept here for reference / the future mobile-app repo.)*

```
app/lib/
├── core/        # dio client, interceptors (auth, retry, error mapping), env, theme (tokens from Figma), routes, widgets
├── data/        # models (json_serializable), repositories (one per module), local cache (GetStorage/Hive)
├── features/<module>/
│   ├── bindings/  controllers/  views/  widgets/
└── calculators/ # pure Dart, zero Flutter imports — 100% unit tested
```

- GetX for state, routing and DI; Dio with an auth interceptor (bearer token), a 401 → logout handler, and error mapping to the API error codes.
- Offline: cache last successful response per endpoint; show cached data with an "Updated at" label and a retry banner.
- Every screen handles 4 states: loading (skeleton), data, empty (Figma empty states), error (retry).
- Themes and text styles are taken from Figma variables/styles. No magic colors in widgets.
- FCM: foreground banner, background tap → deep link to the relevant screen (`bfinz://gold?city=…`).
- Env via `--dart-define=API_BASE_URL=…`; flavors: dev, staging, prod.
- Accessibility: text scaling to 1.3× without overflow, semantic labels on icons, min tap target 48dp.
- Indian number formatting everywhere (`₹1,00,000.00`) via one formatter utility.

---
## 10. Testing strategy

### 10.1 Backend (Pest)

> Repo amendment: this repo's existing test suite is plain PHPUnit, not Pest (`tests/Feature`, `tests/Unit`, PHPUnit assertions). Keep using PHPUnit for consistency unless/until the team decides to migrate.

| Level | What | Rules |
|---|---|---|
| Unit | Services: rate derivation, EMI/FD/RD maturity (server copies), alert crossing logic, eligibility rules, goal recommendation & monthly-required, tax engine, lender fuzzy match, holiday generation (2nd/4th Saturday) | No DB where possible; table-driven datasets |
| Feature | **Every endpoint**: happy path, validation errors (422 with field names), auth (401 on 🔒), not found, pagination, filters, envelope shape | Use `RefreshDatabase`, factories, `assertJsonStructure` + OpenAPI response validation |
| Provider adapters | Parse real sample payloads stored in `tests/Fixtures/providers/*.json`; malformed payload; timeout; 5xx | `Http::fake()` only; `Http::preventStrayRequests()` in `TestCase` |
| Jobs | Sync jobs write correct rows, are idempotent (run twice = same data), mark `sync_runs`, keep last good data on failure, dispatch `EvaluateAlerts` | `Queue::fake()`, `Bus::fake()` |
| Alerts | above/below crossing, re-arm, any_change once/day, inactive alerts ignored, FCM payload built correctly | FCM client faked |
| Auth | OTP hashing, expiry, attempt limits, resend throttle, token issue/revoke, account deletion | `Carbon::setTestNow()` |
| Security | Rate limits return 429; user A cannot read/modify user B's goals/alerts/calculations/notifications (policy tests for every 🔒 resource) | |
| Admin | Filament resources render; data_editor cannot manage admins; audit log written on edit | Livewire tests |
| Scheduler | `schedule:list` contains every job at the right cron | |

Quality gates: `php artisan test --parallel --coverage --min=80`, Larastan passes, Pint clean.

### 10.2 Flutter

*(Out of scope for this repo — see the amendment note at the top of this file.)*

| Level | What |
|---|---|
| Unit | Every calculator (reference vectors below), formatters (Indian numbering, amount-in-words), repositories with mocked Dio, controllers (loading/data/empty/error transitions) |
| Widget | Every screen in its 4 states; form validation (mobile, OTP, calculator inputs); text scale 1.3× no overflow |
| Integration (`integration_test`) | Login → Home; Gold: change city → set alert → see in alerts list; FD compare 3 banks; Goal: create marriage goal → pick plan → summary; IFSC 4-step lookup; Notification delete; Logout |
| Mock server | Integration tests run against a local mock API built from `docs/openapi.yaml` (e.g. Prism) — no real backend needed in CI |

Quality gates: `flutter analyze` zero issues, `flutter test --coverage` ≥ 80% on `calculators/` (target 100%) and ≥ 70% overall.

### 10.3 Calculator reference vectors (must pass exactly, rounded to 2 dp)
| Calculator | Input | Expected |
|---|---|---|
| EMI (reducing) | P ₹5,00,000, 8.25% p.a., 36 months | ₹15,725.91 |
| EMI | P ₹5,00,000, 8.25% p.a., 60 months | ₹10,198.13 |
| EMI | P ₹5,00,000, 8.25% p.a., 72 months | ₹8,827.78 |
| FD (quarterly compounding) | ₹1,00,000, 7.25%, 1 year | ₹1,07,449.50 |
| FD | ₹1,00,000, 7.25%, 5 years | ₹1,43,226.06 |
| RD (quarterly compounding per instalment) | ₹5,000/month, 7%, 12 months | ₹62,310.66 |
| SIP (start of month) | ₹10,000/month, 12% p.a., 10 years | ₹23,23,390.76 |
| Lumpsum | ₹1,00,000, 12% p.a., 10 years | ₹3,10,584.82 |
| GST add | ₹1,000 @ 18% | ₹1,180.00 |
| GST remove | ₹1,180 incl. 18% | ₹1,000.00 |

Formulas: EMI = `P·r·(1+r)^n / ((1+r)^n − 1)`, r = annual/12. FD = `P·(1 + R/4)^(4t)`. RD = `Σ m·(1+R/4)^(4·(n−k)/12)` for k = 0..n−1. SIP = `m·(((1+i)^n − 1)/i)·(1+i)`. Reverse EMI solves P from EMI. Quarterly EMI uses r = annual/4, n = quarters. Inflation: `future = present·(1+inf)^years`. DTI = `total monthly debt payments / gross monthly income × 100`.

Tax engine: create fixtures per assessment year from the **official Income Tax Department slabs** (Claude Code must not invent slab values — seed them from data Pavi's team confirms, and write tests from worked examples published by the department). Tests must cover: income below rebate limit → 0 tax, each slab boundary, cess, old vs new regime comparison, age categories (old regime).

### 10.4 CI (GitHub Actions)
- `backend.yml`: MySQL + Redis services → composer install → migrate → Pint --test → Larastan → Pest (parallel, coverage gate).
- `app.yml`: flutter pub get → analyze → test --coverage (gate) → build apk (debug) on PRs; integration tests against Prism mock on main.
- PRs cannot merge unless both pipelines are green.

---

## 11. Delivery phases

Each phase ends with: all tests green, PROGRESS.md updated, OpenAPI updated, demo notes (what to click to verify).

| Phase | Scope | Acceptance criteria |
|---|---|---|
| **P0 Foundation** | Monorepo, Laravel + Filament + Sanctum + Horizon, Flutter skeleton (GetX, Dio, flavors, theme from Figma), CI pipelines, envelope/error handling, masters (states, cities, banks), `/app/config` | CI green on an empty feature; app launches in 3 flavors; `/master/*` feature tests pass |
| **P1 Auth & Profile** | OTP login (fake sender in dev), Sanctum, devices, profile, account deletion, Login/OTP screens | Full login flow works on device against dev API; all OTP security tests pass |
| **P2 Market data** | Gold, Silver, Forex, Fuel: providers, sync jobs, endpoints, admin override, screens incl. trend charts & calculators | Sync jobs idempotent; screens show "Updated at"; stale flag works when provider fails |
| **P3 Alerts & Notifications** | Alert CRUD, `EvaluateAlerts`, FCM, notifications list/tabs/delete, broadcast from admin | A rate crossing in a test triggers exactly one push + one notification row |
| **P4 Home & Search** | `/home` aggregate, search + history, Home screen, bottom nav | Home renders from one API call; search groups results |
| **P5 Bank products** | FD, RD, Savings, Loans: admin CRUD + CSV import, list/filter/compare/detail, eligibility, leads with consent, calculators | Compare maturity/EMI values equal Flutter calculator values (shared vectors) |
| **P6 Goals & Investments** | Goals CRUD, recommendations, plans, summary, MF sync (AMFI), govt schemes, compare | Marriage/Home/Retirement flows match Figma end to end; monthly-required unit-tested |
| **P7 Finance Tools** | All calculators, saved calculations, net worth tracker, tax slabs + `/tax/calculate` | All reference vectors pass; tax tests from official worked examples pass |
| **P8 Info & Safety** | RBI module + RSS sync, Compliance Corner (articles, directory, lender verify, downloads, videos, economic dashboard + CPI sync), Cyber Crime Help Center (+ complaint letter PDF) | Content manageable fully from admin; lender verify returns correct status on fixtures |
| **P9 Banking Utilities** | Bank holidays, IFSC import + 4-step picker, MICR, ATM/branch locator, banking forms, cheque guidance, amount-in-words | IFSC import of full dataset < 10 min; locator returns sorted by distance |
| **P10 Hardening & Release** | Performance (p95 < 300 ms cached endpoints), security review, crash reporting (Firebase Crashlytics), analytics events, Play Store / App Store assets & data-safety answers, privacy policy & terms screens | Load test report; zero critical findings; release builds signed |

---

## 12. Definition of Done (every task)

- [ ] Matches Figma (labels, states, empty/error screens) — verified via Figma MCP
- [ ] Endpoint documented in `docs/openapi.yaml`
- [ ] Unit + feature/widget tests written and passing; no skipped tests
- [ ] Authorization policy tested for user-owned data
- [ ] Cache TTL and invalidation on admin edit implemented
- [ ] Admin can manage the data (if admin-managed)
- [ ] Lint/static analysis clean
- [ ] `PROGRESS.md` updated

---

## 13. Open questions for Pavi (Claude Code: ask before building these parts)

1. OTP channel: WhatsApp (Meta Cloud API or Gupshup) or SMS provider (MSG91 / 2Factor)? Which account/keys?
2. Gold provider: Metals-API or GoldAPI.io (plan tier)? City premium values for launch cities?
3. Fuel price source: admin CSV upload only, or approve a scraper?
4. Loan leads: are there partner banks/NBFCs to send leads to, or store only?
5. Launch coverage: which banks (top 15–20?) and which states/cities for rates at launch?
6. SGB: keep "Retirement – SGB" (no new tranches recently) or replace with Gold ETF / digital gold info?
7. CPI: confirm the current MoSPI base year to display (Figma shows 2012 = 100).
8. Profile tab screens: design pending — build minimal version now?
9. Tax: which assessment years to support at launch, and who confirms slab data?
10. Hosting: server/provider for API, Redis, and backups; domain for API (`api.bfinz.in`?).
