# 04 — Security Architecture

## 1. Authentication

### 1.1 Web (Admin + Tenant browser)

- **Mechanism:** Laravel Breeze session auth.
- **Storage:** `sessions` table (database driver).
- **CSRF:** Laravel CSRF tokens on all POST/PUT/DELETE forms (`@csrf` in Blade).
- **Password hashing:** bcrypt (Laravel default `Hash::make`).
- **Password reset:** Breeze-generated reset flow via email link.
- **Email verification:** optional, controlled by `MustVerifyEmail` interface (not

  enforced for tenants in current build).

### 1.2 API (Mobile — Flutter)

- **Mechanism:** Laravel Sanctum (token-based).
- **Token abilities:** every token is issued with `ability: braga8_auth_token`.

  API routes use `auth:sanctum` + `ability:braga8_auth_token` middleware.

- **Token storage:** `personal_access_tokens` table (Sanctum default).
- **Login restriction:** `Api\AuthController@login` rejects users with `role=admin` —

  mobile platform is tenant-only. This prevents admin credential abuse via the mobile
  attack surface.

- **Logout:** revokes the current request token (`$user->currentAccessToken()->delete()`).

## 2. Authorization

### 2.1 Role model

Two roles only, stored on `users.role` enum:

| Role | Web access | API access |
| ------ | ----------- | ----------- |
| `admin` | Full CRUD on all modules. | **Denied** (login blocked). |
| `tenant` | Limited: own invoices, payments, complaints. | Full mobile app access. |

### 2.2 Enforcement points

| Layer | Mechanism |
| ------- | ----------- |
| Web routes | `CheckRole:admin` / `CheckRole:tenant` middleware. |
| API routes | `auth:sanctum` + inline role check in `Api\AuthController@login`. |
| Controller logic | Tenant-scoped queries: `Invoice::where('tenant_id', auth()->user()->tenant->id)`. |
| Blade views | `@can` / `@role` directives to hide admin-only UI. |

### 2.3 Tenant data isolation

- A tenant can **only** see their own invoices, payments, complaints, units.
- Controllers filter by `auth()->user()->tenant->id` — never trust a `tenant_id`

  parameter from the client without re-scoping.

- API endpoints return 404 (not 403) when a tenant requests another tenant's resource,

  to avoid information leakage about resource existence.

## 3. Input Validation

- **Web:** Form Requests (`app/Http/Requests/*`) validate every mutating endpoint.

  Failed validation → redirect back with errors (Blade) or JSON 422 (API).

- **Mass assignment:** all models use `$fillable` whitelists — no `guarded = []`.
- **File uploads:** payment proof images validated as `image` MIME, max 2 MB.

  Stored outside `public/` until `storage:link` is run; served via symlinked path.

- **Numeric inputs:** meter readings, amounts validated as `numeric` + `min:0`.

## 4. CSRF & CORS

- **CSRF (web):** Laravel's `VerifyCsrfToken` middleware on all web routes. API

  routes are stateless and excluded from CSRF.

- **CORS (api):** `config/cors.php` allows the Flutter app's origin. In production,

  restrict `allowed_origins` to the mobile app's backend gateway (or `*` if the
  Flutter app uses native HTTP, which has no origin).

## 5. Audit Logging

`AuditLog` middleware wraps admin write routes and records:

| Field | Value |
| ------- | ------- |
| `user_id` | Authenticated admin's id. |
| `action` | HTTP method + path (e.g. `POST /invoices`). |
| `module` | Controller class name. |
| `details` | JSON of request input, **password fields redacted**. |
| `ip_address` | Client IP. |

Audit logs are **append-only** — no update/delete routes exist. Admins can view via
`AuditLogController@index` (read-only).

## 6. Sensitive Data Handling

| Data | Storage | Protection |
| ------ | --------- | ----------- |
| Passwords | `users.password` | bcrypt hash. Never logged, never returned in API responses. |
| Payment proof images | `storage/app/public/payments/` | Filename is randomized (Laravel `Storage::putFile`). Publicly accessible via URL — **recommend moving to private disk + signed URLs in future hardening pass**. |
| Tenant PII (phone, ID number) | `tenants` table | Stored in plaintext. Access restricted by role. |
| Sanctum tokens | `personal_access_tokens` (sha256 hash) | Tokens are hashed at rest (Sanctum default). |
| Audit log details | `audit_logs.details` | Password fields redacted before insert. |

## 7. WhatsApp Notification Flow

- No third-party WhatsApp Business API integration.
- Admin clicks a "Notify via WhatsApp" button → server builds a `wa.me/<phone>?text=<msg>`

  deep link → 302 redirect → opens WhatsApp in the admin's browser/device with the
  message pre-filled.

- **Security implication:** the admin is the human sender, so message content is

  controlled and reviewable before sending. No automated outbound messaging.

## 8. Known Security Considerations

These are tracked in `SECURITY_AUDIT_REPORT.md` and addressed via the custom Semgrep
ruleset in `.semgrep/braga8-custom.yml`:

1. **Payment proof images are publicly accessible** via `storage:link`. Mitigation

   recommendation: move to `storage/app/private/` and serve through a controller
   that checks ownership.

2. **No rate limiting on API login.** Recommendation: add `throttle:5,1` to

   `/api/login` to slow credential stuffing.

3. **Tenant PII stored in plaintext.** Acceptable for current threat model but should

   be revisited if compliance scope expands.

4. **No MFA.** Single-factor password auth for both web and API. Future: enforce

   MFA for admin role via Breeze 2FA or custom TOTP.

## 9. Security Headers

Laravel's default middleware stack sets:

- `X-Frame-Options: SAMEORIGIN` (clickjacking mitigation).
- `X-Content-Type-Options: nosniff`.
- `X-XSS-Protection: 1; mode=block` (legacy, retained).

**Recommendation:** add `Content-Security-Policy` and `Strict-Transport-Security`
headers in production via middleware or web server config.

## 10. Dependency Security

- **Composer audit:** run `composer audit` in CI to scan for known vulnerabilities in

  PHP dependencies.

- **npm audit:** run `npm audit --production` for frontend build dependencies.
- **Semgrep:** `.semgrep/braga8-custom.yml` contains project-specific rules

  (SQL injection patterns, mass assignment, hardcoded credentials, insecure file
  upload) run in CI on every PR.
