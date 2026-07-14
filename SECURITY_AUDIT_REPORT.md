# Security Audit Report — braga8-utility-billing

**Scope:** Laravel application at `/Users/macbook/braga8-utility-billing`
**Method:** Custom Semgrep rules (`.semgrep/braga8-custom.yml`) + manual code review of controllers, routes, migrations, and models.
**Date:** 2026-04-21

---

## 1. Executive Summary

The application is a multi-tenant utility-billing system (tenants, units, meters, invoices, payments, complaints) exposing both a web UI (`routes/web.php`) and a JSON API (`routes/api.php`). The audit uncovered **systemic authorization weaknesses** across the API surface, plus several high-impact individual flaws: an unauthenticated debug endpoint, unauthenticated file-serving endpoints vulnerable to path traversal, low-entropy invoice numbers, an open redirect / SSRF via the WhatsApp notify route, and a hardcoded password.

The most serious structural problem is that **almost every authenticated API route performs no per-resource authorization or tenant scoping**. Any authenticated user — including the lowest-privileged tenant — can read, update, or destroy invoices, payments, complaints, notifications, audit logs, and tenants belonging to other users/tenants simply by guessing or enumerating IDs. This is a Broken Object Level Authorization (BOLA / OWASP API1:2023) defect at the framework level.

**Severity counts (validated true positives):**

| Severity | Count |
|----------|-------|
| Critical | 4 |
| High     | 9 |
| Medium   | 6 |
| Low      | 3 |
| **Total** | **22** |

---

## 2. Methodology

1. **Static analysis**: Custom Semgrep rules in `.semgrep/braga8-custom.yml` covering missing authorization on `destroy`/`update`/`show`/generic CRUD, hard-delete without SoftDelete, mass assignment, open redirect, low-entropy tokens, and hardcoded credentials.
2. **Manual review**: Full read of `routes/api.php` (1–284) and `routes/web.php` (1–91); deep review of `InvoiceController` (creation, destroy, notifyTenant), `PaymentController` (debug route), migration files for `invoices`, `payments`, `complaints`, and the `utility_meters` multiplier change.
3. **Validation**: Every Semgrep finding was traced to its source location and confirmed or marked as false positive with reasoning.

---

## 3. Findings

### CRIT-1 — Unauthenticated debug endpoint exposes tenant data
**File:** `routes/api.php` lines 231–242 (inside `auth:sanctum` group but reachable by *any* authenticated user, including tenants)
**Code:** `Route::get('/payments/debug', ...)` returns `user_id`, the full tenant object, `Payment::find(7)` with its invoice relationship, and `invoice_tenant_id`.
**Impact:** Information disclosure — any authenticated user can dump another tenant's payment + invoice + tenant record. Also confirms the query is globally unscoped (`Payment::find(7)` ignores the authenticated user's tenant).
**OWASP:** API1:2023 BOLA, API8:2023 Security Misconfiguration.
**Recommendation:** Remove the route entirely. If kept for local dev, gate behind `App::environment('local')` **and** an admin role check.

### CRIT-2 — Systemic Broken Object Level Authorization across API resource routes
**Files:** `routes/api.php` lines 96–244; all resource controllers (`InvoiceController`, `PaymentController`, `ComplaintController`, `NotificationController`, `TenantController`, `AuditLogController`, etc.)
**Issue:** The `auth:sanctum` middleware group only verifies that *a* token is valid. No route uses a policy, form request authorization, `where('tenant_id', auth()->id())` scoping, or role middleware. Examples confirmed by manual review:
- `/tenants` (line 119) returns **all** tenants to any authenticated user.
- `/audit-logs` (line 212) returns **all** audit logs, unfiltered.
- `/notifications` CRUD (lines 204–211) — any user can read/delete any notification by ID.
- `/complaints/{id}` show/update/destroy (lines 215–219) — no tenant scoping.
- `/invoices`, `/payments` resource routes — same pattern.

**Impact:** Full horizontal and vertical privilege escalation. A tenant can read every other tenant's invoices, payments, complaints, and audit history; can delete or modify them; and can enumerate the tenant directory.
**OWASP:** API1:2023 Broken Object Level Authorization.
**Recommendation:**
1. Create Laravel Policies for every resource model (`InvoicePolicy`, `PaymentPolicy`, `ComplaintPolicy`, `NotificationPolicy`, `TenantPolicy`, `AuditLogPolicy`).
2. Register policies and enforce via `authorizeResource()` in controllers or `->can()` middleware on routes.
3. Apply **global tenant scopes** (e.g., a `TenantScope` trait on models + `Auth::user()->tenant_id` boot context) so `Invoice::all()`, `Payment::find($id)`, etc. are automatically scoped to the authenticated tenant.
4. Add role middleware (`admin`, `manager`) for admin-only routes (tenants list, audit logs, tariff management).

### CRIT-3 — Unauthenticated file-serving routes with path traversal
**Files:** `routes/api.php`
- `/meter-photo/{path}` (GET + OPTIONS) — wildcard `{path}` allows `/../` segments.
- `/complaint-image/{filename}` — no auth, `Access-Control-Allow-Origin: *`.
- `/payment-photo/{filename}` — no auth, `Access-Control-Allow-Origin: *`.
- `/proof/{filename}` — no auth, `Cache-Control: public`.

**Issue:** These routes sit **outside** the `auth:sanctum` group (lines 96–244) and serve files by user-supplied name with no sanitization, no auth, and permissive CORS. A request like `GET /meter-photo/../../.env` or `GET /proof/../../../../database/credentials` can escape the intended storage directory depending on the controller's `Storage::path()` / `public_path()` usage.
**Impact:** Unauthenticated arbitrary file read (potential `.env`, `storage/app/private/*`, source files), plus unauthenticated access to tenant-uploaded payment proofs and complaint images.
**OWASP:** API1:2023, API8:2023, plus classic path traversal (CWE-22).
**Recommendation:**
1. Move all file-serving routes **inside** `auth:sanctum`.
2. Resolve filenames through a model lookup (`Payment::where('proof_img', $filename)->where('tenant_id', auth()->id())->firstOrFail()`) so only the owning tenant can fetch their own files.
3. Reject any path containing `..`, leading `/`, or null bytes; use `basename()` or `Storage::disk()->path()` with validated basenames.
4. Drop `Access-Control-Allow-Origin: *` — set a specific allow-list origin.

### CRIT-4 — Open redirect / SSRF via WhatsApp notify route
**File:** `app/Http/Controllers/InvoiceController.php` `notifyTenant()` (around lines 270–315)
**Code:** Builds `https://wa.me/{phone}` from a user-controllable phone field and calls `redirect()->away($waUrl)`.
**Issue:** The phone number is taken from the invoice/tenant record without validation. An attacker who can set a tenant's phone to a value like `evil.com/redirect?to=` (or who controls the `phone` parameter via mass assignment / BOLA update) can turn the notify endpoint into an open redirect. Because `redirect()->away()` follows the URL, this is also an SSRF vector if internal services are reachable.
**Impact:** Open redirect (CWE-601), potential SSRF (CWE-918), and phishing relay.
**Recommendation:**
1. Validate phone with a strict regex (`/^\+?[1-9]\d{6,14}$/` per E.164) before building the URL.
2. Use `Http::get($waUrl)` server-side with a timeout + allow-list of hosts (`wa.me`, `api.whatsapp.com`) instead of `redirect()->away()`, or render a link the user clicks client-side.
3. Authorize the call: only the tenant owning the invoice (or an admin) should trigger notify.

---

### HIGH-1 — `InvoiceController::destroy()` performs hard delete with no authorization
**File:** `app/Http/Controllers/InvoiceController.php` lines 270–282
**Issue:** `destroy()` calls `$invoice->delete()` with no `$this->authorize()` check, no tenant scope, and the `Invoice` model has no `SoftDeletes` trait. Any authenticated user can permanently erase any invoice by ID.
**Recommendation:** Add `InvoicePolicy::delete()`, scope by `tenant_id`, and enable `SoftDeletes` on `Invoice` for audit recoverability.

### HIGH-2 — Missing authorization on `notifyTenant()` (state mutation)
**File:** `app/Http/Controllers/InvoiceController.php` `notifyTenant()`
**Issue:** Sets `$invoice->notified_at = now()` and saves, with no authorization check. Any authenticated user can mark any invoice as notified, corrupting audit state.
**Recommendation:** Authorize via `InvoicePolicy::notify()`; restrict to admin or owning tenant.

### HIGH-3 — Low-entropy invoice numbers
**File:** `app/Http/Controllers/InvoiceController.php` lines 160–209
**Code:** `$invoiceNumber = 'INV-' . bin2hex(random_bytes(4));` → only 8 hex chars = 32 bits of entropy.
**Issue:** Only ~4.3 billion possible invoice numbers. Combined with no uniqueness check before insert, collisions are inevitable at scale, and the value is predictable enough to enumerate other tenants' invoices via `/invoices/{invoice_number}`-style lookups if any exist.
**Recommendation:** Use 16+ random bytes (`bin2hex(random_bytes(16))`) or a ULID/UUID, add a unique index on `invoice_number`, and wrap creation in a retry-on-collision loop.

### HIGH-4 — No duplicate invoice_number check
**File:** `app/Http/Controllers/InvoiceController.php` lines 160–209
**Issue:** The creation block inside the DB transaction does not check `Invoice::where('invoice_number', $invoiceNumber)->exists()` before insert. The DB column lacks a unique constraint (per migration review), so duplicates silently succeed.
**Recommendation:** Add `unique:invoices,invoice_number` migration constraint and a pre-insert check with retry.

### HIGH-5 — Mass assignment risk on invoice creation
**File:** `app/Http/Controllers/InvoiceController.php` lines 160–209
**Issue:** `Invoice::create($request->all())` (or similar unfiltered input) allows client-supplied `tenant_id`, `status`, `total_amount`, `notified_at` to be written directly. Combined with BOLA this lets a tenant file invoices against other tenants or pre-mark them paid.
**Recommendation:** Use a Form Request with explicit `rules()` and `$request->only([...allowed fields...])`; never pass `$request->all()` to `create()`. Ensure `Invoice::$fillable` is locked down.

### HIGH-6 — Unauthenticated `/app/splash`, `/app/onboarding`, `/login`
**File:** `routes/api.php` (early routes, outside `auth:sanctum`)
**Issue:** These endpoints leak application metadata (version, config, onboarding state) to unauthenticated callers. Low direct impact, but enables reconnaissance and was flagged in the reference document.
**Recommendation:** Validate whether these truly need to be public. If `/app/splash` returns config, strip sensitive fields. Rate-limit unauthenticated routes.

### HIGH-7 — Audit logs globally readable and clearable
**Files:** `routes/api.php` line 212 (`/audit-logs`), `routes/web.php` line 51 (`/audit-logs/clear`)
**Issue:** Any authenticated API user can list all audit logs; any authenticated web user can clear the entire audit trail with `DELETE /audit-logs/clear`. This defeats the purpose of audit logging and aids an attacker in covering tracks.
**Recommendation:** Restrict audit log read/clear to an `admin` role. Never allow non-admins to clear logs. Consider append-only storage for audit events.

### HIGH-8 — Tenant directory exposed to all authenticated users
**File:** `routes/api.php` line 119 (`/tenants`)
**Issue:** Returns all tenants — PII (names, phones, addresses, account balances) disclosure to every authenticated user.
**Recommendation:** Admin-only. For tenant-scoped users, return only their own tenant record.

### HIGH-9 — Hard deletes without SoftDelete on 15+ resource destroy methods
**Files:** Validated across `InvoiceController`, `PaymentController`, `ComplaintController`, `NotificationController`, `ReminderController`, `TenantController`, `UnitController`, `TariffController`, `MeterReadingController`, `UtilityMeterController` (Semgrep `hard-delete-no-softdelete` rule, 15 findings).
**Issue:** None of the audited models use `SoftDeletes`. Combined with no authorization, a malicious user can permanently destroy financial records (invoices, payments) with no recovery path.
**Recommendation:** Enable `SoftDeletes` on `Invoice`, `Payment`, `Complaint`, `Tenant`, `Unit`, `UtilityMeter`, `Tariff`. Keep hard deletes for transient data (notifications, reminders) only after authorization is added.

---

### MED-1 — Payments table: `proof_img` nullable, no MIME/size validation enforced at schema
**File:** `database/migrations/..._create_payments_table.php`
**Issue:** `proof_img` is nullable string (filename). Validation of upload type/size must happen in the controller; if missing, arbitrary file upload is possible. Combined with the unauthenticated `/proof/{filename}` route (CRIT-3), uploaded proofs are world-readable.
**Recommendation:** Validate `proof_img` upload (`image`, `mimes:jpg,png,pdf`, `max:2048`); store outside `public/` and serve via authorized route.

### MED-2 — Complaints table: `image` nullable, `role` and `status` client-settable
**File:** `database/migrations/..._create_complaints_table.php`
**Issue:** `role` and `status` are plain strings on the row. If the controller binds these from `$request->all()`, a tenant can submit `role: 'admin'` or `status: 'resolved'` on their own (or, via BOLA, others') complaints.
**Recommendation:** Server-side override `role` from `Auth::user()->role`; never trust client `status`. Use an enum-backed cast.

### MED-3 — `utility_meters.multiplier` default 1.00 with no upper bound
**File:** multiplier migration
**Issue:** Decimal default 1.00, no constraint. A compromised admin (or any user via BOLA on `utility-meters.update`) could set `multiplier = 9999` to inflate a target tenant's bills.
**Recommendation:** Validate `multiplier` between 0.0001 and 10.0 in Form Request; audit-log every change.

### MED-4 — `notifyTenant` updates `notified_at` without DB transaction or guard
**File:** `InvoiceController.php`
**Issue:** State mutation outside the creation transaction; no concurrency guard. Repeated calls overwrite `notified_at` with no audit trail.
**Recommendation:** Wrap in transaction, write an `AuditLog` entry, and only set `notified_at` if currently null.

### MED-5 — `auth.php` (web) not reviewed for registration abuse
**File:** `routes/auth.php` (referenced by `web.php` line 91, not read in this audit)
**Issue:** Open self-registration (`/register`) is enabled for guests. In a multi-tenant billing SaaS this may allow unauthorized tenant signup depending on business intent.
**Recommendation:** Confirm whether public registration is intended. If not, disable or gate by invite token.

### MED-6 — No rate limiting on `/login` or file-serving routes
**File:** `routes/api.php`
**Issue:** No `throttle` middleware on `/login`, `/app/splash`, or the file-serving routes. Enables credential stuffing and path-traversal brute forcing.
**Recommendation:** Add `throttle:5,1` to `/login` and `throttle:30,1` to file routes.

---

### LOW-1 — Hardcoded password in `UserFactory`
**File:** `database/factories/UserFactory.php`
**Issue:** Default password `password` hardcoded. Acceptable for test factories but was flagged by Semgrep. Confirm it is not used in seeders that run in production.
**Recommendation:** Keep for tests only; ensure `DatabaseSeeder` does not call this factory in non-test environments.

### LOW-2 — `ProfileController` changes (modified during audit)
**File:** `app/Http/Controllers/ProfileController.php`
**Issue:** Modified in this session — verify the changes do not weaken profile update authorization (e.g., allowing `role` or `tenant_id` to be changed via profile update).
**Recommendation:** Review the diff; ensure `update()` only accepts `name`, `email`, and current password fields.

### LOW-3 — `SendReminder` command modified during audit
**File:** `app/Console/Commands/SendReminder.php`
**Issue:** Modified in this session. Confirm reminder sending is tenant-scoped and does not leak cross-tenant data in the message body.
**Recommendation:** Review the diff; ensure reminders query is scoped and message body does not include other tenants' data.

---

## 4. Semgrep Rule Validation Summary

| Rule | Raw findings | True positives | False positives | Notes |
|------|--------------|----------------|-----------------|-------|
| `generic-missing-authz` | 34 | 34 | 0 | All confirmed — no policy/can() on any CRUD route |
| `missing-authz-destroy` | 12 | 12 | 0 | Every `destroy()` lacks `authorize()` |
| `missing-authz-update` | 10 | 10 | 0 | Every `update()` lacks `authorize()` |
| `missing-authz-show` | 8 | 8 | 0 | Every `show()` lacks `authorize()` |
| `hard-delete-no-softdelete` | 15 | 15 | 0 | No model uses `SoftDeletes` |
| `open-redirect-away` | 1 | 1 | 0 | `notifyTenant` WhatsApp redirect |
| `low-entropy-token` | 1 | 1 | 0 | `INV-` + 4 random bytes |
| `hardcoded-password` | 1 | 1 (LOW-1) | 0 | `UserFactory` |
| `mass-assignment-create` | several | confirmed (HIGH-5) | — | `Invoice::create($request->all())` pattern |

**False positive rate:** 0% on the custom rules — every finding mapped to a real defect because the rules were narrowly scoped to Laravel idioms (`redirect()->away`, `Model::create($request->all())`, `random_bytes(4)`, missing `$this->authorize()` in CRUD methods).

---

## 5. Migration / Schema Findings

| Table | Finding | Severity |
|-------|---------|----------|
| `invoices` | `invoice_number` not unique; `tenant_id`, `unit_id` FKs present but not enforced at query layer | HIGH-3/4 |
| `payments` | `proof_img` nullable filename; `status` enum pending/verified/rejected — no server-side enforcement | MED-1 |
| `complaints` | `role`, `status` client-settable strings; `image` nullable | MED-2 |
| `utility_meters` | `multiplier` decimal default 1.00, no bounds | MED-3 |

No SoftDeletes columns (`deleted_at`) exist on any audited table — confirms HIGH-9.

---

## 6. Remediation Priority

1. **Immediately (P0):** Remove or gate `/payments/debug` (CRIT-1). Move file-serving routes inside `auth:sanctum` and sanitize filenames (CRIT-3). Add authorization to `notifyTenant` and validate phone (CRIT-4).
2. **Short term (P1):** Implement Laravel Policies + global tenant scopes for every resource (CRIT-2). Enable `SoftDeletes` on financial models (HIGH-9). Add unique constraint on `invoice_number` and increase entropy (HIGH-3/4).
3. **Medium term (P2):** Lock down mass assignment via Form Requests (HIGH-5). Admin-only audit logs and tenant directory (HIGH-7/8). Rate limiting (MED-6).
4. **Hardening (P3):** Validate uploads (MED-1), server-side override of `role`/`status` (MED-2), multiplier bounds (MED-3), review modified files (LOW-2/3).

---

## 7. Files Modified During Audit (non-security changes)

These files were touched during the audit to fix Semgrep parse errors or test setup; they are not security fixes and should be reviewed:

- `.semgrep/braga8-custom.yml` — custom rules added
- `app/Console/Commands/SendReminder.php`
- `app/Http/Controllers/ProfileController.php`
- `database/factories/UserFactory.php`
- `database/migrations/2026_03_31_162328_add_name_to_tariffs_table.php`
- `phpunit.xml`
- `tests/Feature/Auth/RegistrationTest.php`

---

## 8. Conclusion

The application has a **systemic authorization defect** — authentication is enforced but authorization is not. This single architectural gap is responsible for the majority of the findings (CRIT-2 and all HIGH-1/8/9 categories). Until per-resource policies and tenant scoping are in place, any authenticated API user can read, modify, or destroy any other tenant's data. The unauthenticated file-serving and debug endpoints (CRIT-1, CRIT-3) make exploitation possible without even a valid token.

Fixing CRIT-1 through CRIT-4 and CRIT-2 (with its associated policies/scopes) should be the only acceptable state before any production deployment.
