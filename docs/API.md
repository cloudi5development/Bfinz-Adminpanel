# Bfinz API Reference

Mirrors [`Bfinz-API-Documentation.xlsx`](Bfinz-API-Documentation.xlsx) (same endpoints, same format: Title / Method / URL / Form Data / Success / Failure). Keep both in sync when an endpoint changes — this file is the git-diffable version, the spreadsheet is the shareable one.

**Base URL**: `http://localhost/api/v1` (dev). **Envelope**: `{ "status": bool, "data": ..., "message": "..." }` plus extra top-level keys per endpoint (`updated_at`, `stale`, etc.) — see `App\Support\ApiResponse`.

---

## OTP Login API

| # | Title | Method | URL |
|---|---|---|---|
| 1 | Health Check | GET | `/health` |
| 2 | Send OTP | POST | `/auth/send-otp` |
| 3 | Verify OTP (Login) | POST | `/auth/verify-otp` |
| 4 | Get Profile | GET 🔒 | `/auth/profile` |
| 5 | Update Profile | POST 🔒 | `/auth/update-profile` |
| 6 | Login History | GET 🔒 | `/auth/login-history` |
| 7 | Logout (current device) | POST 🔒 | `/auth/logout` |
| 8 | Logout All Devices | POST 🔒 | `/auth/logout-all` |
| 9 | Resend OTP | POST | `/auth/resend-otp` |
| 10 | Delete Profile (Account Deletion) | DELETE 🔒 | `/auth/profile` |
| 11 | Register Device | POST 🔒 | `/auth/devices` |

### 2. Send OTP
- **Body**: `{ "mobile": "9876543210" }`
- **Success**: `{ "status": true, "data": null, "message": "OTP sent successfully." }`
- **Failure 1** (422): `{ "status": false, "message": "Validation failed.", "data": { "mobile": ["..."] } }` — mobile must match `^[6-9]\d{9}$`
- **Failure 2** (429): `{ "status": false, "message": "Please wait 30 seconds before requesting another OTP." }` (resend cooldown) **or** `{ "status": false, "message": "Too many OTP requests for this number. Please try again in an hour." }` (max 5/hour, enforced per mobile **and** per IP)

### 3. Verify OTP (Login)
- **Body**: `{ "mobile": "9876543210", "otp": "123456", "name": "John Doe", "device_name": "iphone-15-pro", "platform": "ios", "fcm_token": "...", "app_version": "1.2.3" }` — all but mobile/otp optional
- **Success**: `{ "status": true, "data": { "token": "1|..." }, "message": "Login successful." }`
- **Failure 1** (422): `{ "status": false, "message": "Invalid OTP. Please try again." }`
- **Failure 2** (422/429): `{ "status": false, "message": "OTP has expired. Please request a new one." }` (5-minute expiry) — also `"No OTP found..."` (422) and `"Too many failed attempts..."` (429, after 5 wrong tries)

### 4. Get Profile
- **Header**: `Authorization: Bearer {token}`
- **Success**: `{ "status": true, "data": { "id": 5, "name": "...", "mobile": "...", "email": null, "is_mobile_verified": true, "mobile_verified_at": "..." } }`
- **Failure** (401): `{ "status": false, "message": "Unauthenticated." }`

### 5. Update Profile
- **Body**: `{ "name": "...", "email": "..." }` (both optional) + Bearer header
- **Success**: `{ "status": true, "data": { "id": 5, "name": "...", "...": "..." }, "message": "Profile updated." }`
- **Failure 1** (422): validation (e.g. invalid email)
- **Failure 2** (401): Unauthenticated

### 6. Login History
- **Header**: Bearer token
- **Success**: `{ "status": true, "data": [ { "id": 1, "device_name": "...", "platform": "...", "ip_address": "...", "logged_in_at": "...", "logged_out_at": null, "is_active": true, "is_current": true } ] }` (last 50, newest first)
- **Failure** (401): Unauthenticated

### 7 / 8. Logout / Logout All
- **Header**: Bearer token
- Logout revokes only the current token; Logout All revokes every Sanctum token the user holds and clears their FCM devices.
- **Failure** (401): Unauthenticated

### 9. Resend OTP
Identical contract to Send OTP (§2) — a distinct route so the client can express "resend" vs. "initial send"; same body, same rate-limit responses.

### 10. Delete Profile (Account Deletion)
- **Header**: Bearer token
- **Success**: `{ "status": true, "data": null, "message": "Account deleted." }` — soft-deletes and anonymises the account (mobile/name/email cleared), revokes all tokens and FCM devices
- **Failure** (401): Unauthenticated

### 11. Register Device
- **Body**: `{ "fcm_token": "...", "device_name": "...", "platform": "android|ios|web", "app_version": "..." }` (all but fcm_token optional) + Bearer header
- **Success**: `{ "status": true, "data": null, "message": "Device registered." }` — upserts by `fcm_token`
- **Failure 1** (422): validation. **Failure 2** (401): Unauthenticated

---

## Master & Config API

| # | Title | Method | URL | Cache |
|---|---|---|---|---|
| 1 | List States | GET | `/master/states` | 24h |
| 2 | List Cities | GET | `/master/cities?state_id=` | 24h |
| 3 | List Banks | GET | `/master/banks?category=` | 6h |
| 4 | App Config | GET | `/app/config` | 10 min |

### 1. List States
`{ "status": true, "data": [ { "id": 1, "name": "Andhra Pradesh", "code": "AP" }, "..." ] }`

### 2. List Cities
`state_id` optional. `{ "status": true, "data": [ { "id": 1, "state_id": 1, "name": "Mumbai", "lat": 19.076, "lng": 72.8777 } ] }`. 422 on an unknown `state_id`.

### 3. List Banks
`category` optional (`public|private|sfb|nbfc|foreign|cooperative`); only `is_active` banks. `{ "status": true, "data": [ { "id": 1, "name": "State Bank of India", "short_name": "SBI", "category": "public", "rating": 4.2 } ] }`. 422 on an unknown category.

### 4. App Config
`{ "status": true, "data": { "min_version": "1.0.0", "force_update": false, "quick_actions": [], "banners": [] } }` — admin-editable via the Settings page.

---

## Market Data API

All read-only, served from DB (never calls a provider during a request — `SyncMetalRates`/`SyncForexRates` write the data on schedule; see `routes/console.php`). `updated_at` + `stale` are extra top-level response keys wherever staleness matters.

| # | Title | Method | URL |
|---|---|---|---|
| 1 | Gold/Silver Live Rate | GET | `/metals/{metal}/live?city_id=` |
| 2 | Gold/Silver Details | GET | `/metals/{metal}/details?city_id=` |
| 3 | Gold/Silver Trend | GET | `/metals/{metal}/trend?purity=&range=` |
| 4 | Gold/Silver Cities | GET | `/metals/{metal}/cities?state_id=` |
| 5 | Forex Rates | GET | `/forex/rates?group=` |
| 6 | Forex Detail + History | GET | `/forex/{code}?range=` |
| 7 | Forex Movers | GET | `/forex/movers` |
| 8 | Forex Currencies | GET | `/forex/currencies` |
| 9 | Fuel Prices | GET | `/fuel/prices?fuel=&state_id=&city_id=` |
| 10 | Fuel Trend | GET | `/fuel/trend?fuel=&city_id=&range=` |
| 11 | Fuel Cities | GET | `/fuel/cities?fuel=&state_id=` |

`{metal}` = `gold\|silver`. `range` = `7d\|1m\|6m\|1y` (default `1m`). `{code}` = 3-letter currency code.

### 1. Gold/Silver Live Rate
`{ "status": true, "data": [ { "purity": "24k", "per_gram": 7000.00, "per_8g": 56000.00, "per_10g": 70000.00, "change": 25.00, "change_pct": 0.36 } ], "updated_at": "...", "stale": false }`. Falls back to the national rate if the city has no premium configured. 404 for an unknown metal.

### 2. Gold/Silver Details
`{ "status": true, "data": { "today": {...}, "seven_day_high": { "rate": 7050.00, "date": "..." }, "seven_day_low": { "rate": 6900.00, "date": "..." } }, "updated_at": "...", "stale": false }`. 404 if no rates yet.

### 3. Gold/Silver Trend
`{ "status": true, "data": [ { "date": "2026-09-16", "rate": 6950.00 }, "..." ] }`. `purity` optional (defaults to the metal's primary purity: 24k gold, 999 silver).

### 4. Gold/Silver Cities
`{ "status": true, "data": [ { "city_id": 5, "name": "Mumbai", "per_gram": 7010.00 } ] }` — only cities with a configured rate.

### 5. Forex Rates
`group` optional (`all\|popular\|asia\|middle_east\|europe\|americas\|other`, default `all`). `{ "status": true, "data": [ { "code": "USD", "rate": 83.50, "change": 0.10, "change_pct": 0.12, "updated_at": "..." } ], "updated_at": "...", "stale": false }`.

### 6. Forex Detail + History
`{ "status": true, "data": { "current": {...}, "history": [ { "date": "...", "rate": 83.10 } ] }, "updated_at": "...", "stale": false }`. 404 for a currency with no rate yet.

### 7. Forex Movers
`{ "status": true, "data": { "top_gainers": [...], "top_losers": [...] } }` — top 5 each by `change_pct` across active currencies.

### 8. Forex Currencies
`{ "status": true, "data": [ { "code": "USD", "name": "US Dollar", "country": "United States", "group": "popular" } ] }` — active currencies only, cached 24h.

### 9. Fuel Prices
`fuel` required (`petrol\|diesel\|cng`). `{ "status": true, "data": [ { "fuel": "petrol", "city_id": 5, "city": "Mumbai", "price": 105.00, "change": 0.20, "updated_at": "2026-09-23" } ] }` — one row per city, latest price. 422 if `fuel` is missing.

### 10. Fuel Trend
`fuel` and `city_id` required. `{ "status": true, "data": [ { "date": "...", "rate": 104.50 } ] }`.

### 11. Fuel Cities
`fuel` required. `{ "status": true, "data": [ { "city_id": 5, "name": "Mumbai", "price": 105.00 } ] }` — only cities with a price entered.

> **No sync job for fuel yet** — the source (admin CSV upload vs. a scraper adapter) is still open (see `docs/PROGRESS.md` §13 Q3). Prices are whatever's in the `fuel_prices` table (seeded/admin-entered). Gold, Silver, and Forex *are* synced automatically by scheduled jobs.

---

## Alerts & Notifications API

All 🔒 (`auth:sanctum`), scoped to the caller. `EvaluateAlerts` runs automatically after every metal/forex sync (see `routes/console.php`) — there's no manual "check now" endpoint. Push notifications currently just log (no FCM credentials yet — spec §13 Q10); swapping in a real sender is one adapter class.

| # | Title | Method | URL |
|---|---|---|---|
| 1 | List Alerts | GET | `/alerts?type=` |
| 2 | Create Alert | POST | `/alerts` |
| 3 | Update Alert | PUT | `/alerts/{alert}` |
| 4 | Delete Alert | DELETE | `/alerts/{alert}` |
| 5 | List Notifications | GET | `/notifications?tab=all\|alerts\|banking\|loans` |
| 6 | Mark Notification Read | PATCH | `/notifications/{id}/read` |
| 7 | Mark All Read | POST | `/notifications/read-all` |
| 8 | Delete Notification | DELETE | `/notifications/{id}` |
| 9 | Clear All Notifications | DELETE | `/notifications` |

### 1/2. List / Create Alert
- **Body** (create): `{ "type": "gold", "asset": "24k", "city_id": null, "condition": "above", "target_value": 7000 }` — `target_value` not required when `condition` is `any_change`. `type`: `gold\|silver\|forex\|fuel\|fd\|rd` (fd/rd accepted but never fire yet — those products don't exist until P5).
- **Success**: `{ "status": true, "data": { "id": 1, "type": "gold", "asset": "24k", "city_id": null, "condition": "above", "target_value": 7000.0, "is_active": true, "last_triggered_at": null }, "message": "Alert created." }`
- **Failure 1** (422): validation. **Failure 2** (422): `{ "status": false, "message": "You can have at most 20 active alerts. Delete or disable one first." }`

### 3/4. Update / Delete Alert
Same body shape as create. **Failure** (403): `{ "status": false, "message": "This action is unauthorized." }` — trying to touch another user's alert.

### 5. List Notifications
`{ "status": true, "data": [ { "id": 1, "category": "alerts", "title": "Gold alert: 24k", "body": "Now at 7050.00 (your alert: above 7000.00)", "data": {...}, "is_broadcast": false, "read_at": null, "created_at": "..." } ], "unread_count": 3 }` — includes broadcasts (`user_id` null rows), paginated (`?per_page=`).

### 6–9. Read / Read-All / Delete / Delete-All
All straightforward `{ "status": true, "data": null, "message": "..." }`. **Note**: broadcast notifications (`is_broadcast: true`) can't be individually marked-read or deleted yet — `{ "status": false, "message": "Broadcast notifications cannot be modified individually yet." }` (403). Per-user read/delete state for broadcasts needs a pivot table, deferred until the admin "send broadcast" feature is actually built (P8).

---

## Home & Search API

| # | Title | Method | URL | Auth |
|---|---|---|---|---|
| 1 | Home | GET | `/home?city_id=` | Public, personalised if a bearer token is sent |
| 2 | Search | GET | `/search?q=` | Public, personalised if a bearer token is sent |
| 3 | Search Suggestions | GET | `/search/suggestions` | Public |
| 4 | Recent Searches | GET | `/search/recent` | 🔒 |
| 5 | Clear Recent Searches | DELETE | `/search/recent` | 🔒 |

### 1. Home
One call renders the whole Home screen: `{ "status": true, "data": { "greeting_name": "Asha", "location": "Mumbai", "gold": { "24k": 7000.00, "22k": 6412.00, "change": 25.00, "updated_at": "..." }, "silver": { "999": 850.00, "change": 2.00, "updated_at": "..." }, "usd_inr": { "rate": 83.50, "change": 0.10, "updated_at": "..." }, "forex_top": [ { "code": "USD", "rate": 83.50, "change_pct": 0.12 } ], "best_fd": [], "loan_banners": [], "quick_actions": [] } }`. `greeting_name` is `"Guest"` and `location` is `null` when no bearer token / no `city_id`. **`best_fd` and `loan_banners` are always empty** — FD/RD and Loans (P5) don't exist in this repo yet. Cached 5 min per `city_id` (the personalised fields are computed fresh every request, not cached).

### 2. Search
`q` required, min 2 characters. `{ "status": true, "data": { "banks": [ { "id": 1, "name": "HDFC Bank", "short_name": "HDFC" } ], "currencies": [ { "code": "USD", "name": "US Dollar" } ] } }`. **Only `banks` and `currencies` exist as real groups so far** — the spec's other groups (`tools`, `ifsc`, `loans`, `deposits`, `articles`) will be added as those modules land (P5/P7/P8/P9). Stores a history row if the caller is authenticated.

### 3. Search Suggestions
Static placeholder pending Finance Tools (P7): `{ "status": true, "data": { "quick_tools": ["Gold Rate", "Silver Rate", "Forex Rates", "Fuel Price"], "categories": ["Banks", "Currencies"] } }`.

### 4/5. Recent / Clear Recent Searches
`{ "status": true, "data": ["gold rate", "silver rate"] }` (last 10, newest first) / `{ "status": true, "data": null, "message": "Search history cleared." }`.
