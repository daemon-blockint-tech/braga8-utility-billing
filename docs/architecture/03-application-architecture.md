# 03 — Application Architecture

## 1. Layered Structure

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php            # Breeze-generated profile
│   │   ├── Controller.php                # Base controller
│   │   ├── DashboardController.php
│   │   ├── InvoiceController.php
│   │   ├── PaymentController.php
│   │   ├── TenantController.php
│   │   ├── UnitController.php
│   │   ├── UtilityMeterController.php
│   │   ├── MeterReadingController.php
│   │   ├── TariffController.php
│   │   ├── ComplaintController.php
│   │   ├── NotificationController.php
│   │   ├── AuditLogController.php
│   │   ├── ProfileController.php
│   │   └── Api/
│   │       ├── AuthController.php        # Mobile login/profile
│   │       ├── InvoiceController.php     # Mobile invoice list
│   │       ├── PaymentController.php     # Mobile payment submit
│   │       └── ComplaintController.php   # Mobile complaints
│   ├── Middleware/
│   │   ├── CheckRole.php
│   │   └── AuditLog.php
│   └── Requests/                         # Form requests (validation)
├── Models/
│   ├── User.php
│   ├── Tenant.php
│   ├── Unit.php
│   ├── UtilityMeter.php
│   ├── MeterReading.php
│   ├── Tariff.php
│   ├── Invoice.php
│   ├── InvoiceItem.php
│   ├── Payment.php
│   ├── Complaint.php
│   ├── AuditLog.php
│   └── Notification.php
├── Console/
│   └── Commands/
│       └── SendReminder.php
└── Providers/
    ├── AppServiceProvider.php
    ├── AuthServiceProvider.php
    └── ...
```

> **No `Services/` directory exists.** Business logic currently lives inside
> controllers. This is a deliberate trade-off for a small team — controllers are
> the single source of truth for each workflow. If the codebase grows, extract
> `app/Services/InvoiceService.php`, `PaymentService.php`, etc.

## 2. Routing

### 2.1 Web routes (`routes/web.php`)

Grouped by middleware:

| Group | Middleware | Controllers |
| ------- | ----------- | ------------- |
| Public | none | `AuthController` login/register. |
| Authenticated | `auth` | `ProfileController`. |
| Admin | `auth`, `CheckRole:admin` | Dashboard, Invoice, Payment, Tenant, Unit, UtilityMeter, MeterReading, Tariff, Complaint, Notification, AuditLog. |
| Tenant (web) | `auth`, `CheckRole:tenant` | Tenant-facing invoice view, payment submit, complaint create. |

All admin routes are prefixed under the role middleware — there is no separate
route prefix; the middleware enforces access.

### 2.2 API routes (`routes/api.php`)

All under `/api` prefix, `auth:sanctum` middleware with `ability:braga8_auth_token`.

| Method | Path | Controller@method |
| -------- | ------ | ------------------- |
| POST | `/api/login` | `Api\AuthController@login` (no auth) |
| POST | `/api/logout` | `Api\AuthController@logout` |
| GET | `/api/profile` | `Api\AuthController@profile` |
| PUT | `/api/profile` | `Api\AuthController@updateProfile` |
| GET | `/api/invoices` | `Api\InvoiceController@index` |
| GET | `/api/invoices/{id}` | `Api\InvoiceController@show` |
| POST | `/api/payments` | `Api\PaymentController@store` |
| GET | `/api/payments` | `Api\PaymentController@index` |
| GET | `/api/complaints` | `Api\ComplaintController@index` |
| POST | `/api/complaints` | `Api\ComplaintController@store` |

## 3. Controllers — Responsibilities

### 3.1 `DashboardController`

- Aggregates KPIs: total tenants, new this month, total invoice value, paid/unpaid,

  overdue count, meter reading progress.

- Builds chart data (monthly usage reports) for Chart.js on the dashboard.

### 3.2 `InvoiceController`

| Method | Behavior |
| -------- | ---------- |
| `index` | Paginated list with search (invoice number, tenant name, unit number, month, year). |
| `create` | Form: select unit, billing period, pulls latest meter readings. |
| `store` | **Core calculation:** for each meter on the unit, compute `usage = current - previous`, look up active tariff, compute cost, create `InvoiceItem`, sum to `Invoice.total_amount`, generate PDF, create notification. |
| `show` | Display invoice + items + payments. |
| `edit` / `update` | Limited edits (notes, due date) — invoice items are not editable after creation. |
| `destroy` | Soft delete (status flag) — preserves audit trail. |
| `notifyWhatsApp` | Builds `wa.me` deep link with pre-filled message and redirects admin. |
| `downloadPdf` | Streams stored PDF (or regenerates on demand). |

### 3.3 `PaymentController`

| Method | Behavior |
| -------- | ---------- |
| `index` | Paginated list, filter by status / invoice / tenant. |
| `create` | Upload form (image proof, amount, invoice select). |
| `store` | Validates, stores image to `storage/app/public/payments`, creates `Payment` (status: pending). |
| `edit` / `update` | Admin edits amount / re-uploads proof. |
| `verify` | Sets `status=verified`, increments `invoice.paid_amount`, flips `invoice.status` to `paid` when fully paid, writes audit log, notifies admins. |
| `reject` | Sets `status=rejected`, notifies tenant. |
| `destroy` | Hard delete (image file also removed). |
| `sendReminder` | WhatsApp reminder deep link. |
| `Api\PaymentController@store` | Mobile: accepts base64-encoded image, decodes, stores, creates payment. |

### 3.4 `Api\AuthController`

- `login`: validates credentials, rejects `role=admin` users (mobile is tenant-only),

  issues Sanctum token with `braga8_auth_token` ability.

- `profile` / `updateProfile`: returns/updates user + linked tenant record.
- `logout`: revokes current token.

### 3.5 Other controllers

- `TenantController` — CRUD for tenant profiles, links to `users` (creates user account

  with `role=tenant` when creating a tenant).

- `UnitController` — CRUD for rental units.
- `UtilityMeterController` — CRUD for meters per unit.
- `MeterReadingController` — Records readings, auto-computes `usage` against previous

  reading for the same meter.

- `TariffController` — CRUD for tariffs; `effective_date` controls which tariff is

  "active" for a given utility type.

- `ComplaintController` — Tenant creates, admin responds, status transitions.
- `NotificationController` — In-app notifications (read/unread).
- `AuditLogController` — Read-only view of `audit_logs` (admin only).

## 4. Middleware

### 4.1 `CheckRole`

```php
// app/Http/Middleware/CheckRole.php (simplified)
if (! Auth::check() || Auth::user()->role !== $role) {
    abort(403);
}
```

Registered as `role` middleware alias. Used as `role:admin` / `role:tenant` in routes.

### 4.2 `AuditLog`

Wraps admin-modifying routes. On request completion, inserts a row into `audit_logs`
with `user_id`, `action` (HTTP method + path), `module` (controller name), `ip_address`,
and `details` (JSON of request input, redacting passwords).

## 5. Models — Key Relationships

```php
// app/Models/User.php
public function tenant(): HasOne   // → tenants.user_id

// app/Models/Tenant.php
public function user(): BelongsTo
public function units(): HasMany
public function complaints(): HasMany

// app/Models/Unit.php
public function tenant(): BelongsTo
public function utilityMeters(): HasMany
public function invoices(): HasMany

// app/Models/UtilityMeter.php
public function unit(): BelongsTo
public function tariff(): BelongsTo       // via utility_type match
public function meterReadings(): HasMany

// app/Models/MeterReading.php
public function utilityMeter(): BelongsTo
public function previousReading(): BelongsTo // self-ref

// app/Models/Invoice.php
public function unit(): BelongsTo
public function tenant(): BelongsTo
public function items(): HasMany           // invoice_items
public function payments(): HasMany

// app/Models/Payment.php
public function invoice(): BelongsTo
public function tenant(): BelongsTo
```

## 6. Scheduled Jobs

`app/Console/Kernel.php` (or `routes/console.php` in Laravel 12) schedules:

```php
$schedule->command('reminders:send')->dailyAt('08:00');
```

`SendReminder` command (`app/Console/Commands/SendReminder.php`):

- Queries invoices where `status != paid` AND `due_date < today + 3 days`.
- For each, creates an in-app notification and (optionally) logs a WhatsApp reminder

  for admins to send manually.

## 7. Presentation Layer

### 7.1 Blade layout

- `resources/views/layouts/app.blade.php` — main admin layout (sidebar nav, top bar).
- `resources/views/layouts/tenant.blade.php` — tenant-facing layout.
- TailwindCSS utility classes throughout; Alpine.js for dropdowns/modals.

### 7.2 Key views

| View | Purpose |
| ------ | --------- |
| `dashboard/index.blade.php` | KPI cards + usage charts. |
| `invoices/index.blade.php` | Searchable invoice table. |
| `invoices/show.blade.php` | Invoice detail + PDF download + WA notify button. |
| `payments/index.blade.php` | Payment verification queue. |
| `payments/create.blade.php` | Upload proof image form. |
| `tenants/*`, `units/*`, `utility-meters/*`, `meter-readings/*`, `tariffs/*` | Standard CRUD forms. |
| `complaints/*` | Complaint thread + admin response form. |
| `audit-logs/index.blade.php` | Read-only audit trail table. |

## 8. Validation

Form Requests live in `app/Http/Requests/`. Each defines `rules()` and `authorize()`:

- `StoreInvoiceRequest` — validates unit_id, billing_period, due_date.
- `StorePaymentRequest` — validates invoice_id, amount, proof_image (image, max 2MB).
- `StoreComplaintRequest` — validates subject, description.
- `StoreMeterReadingRequest` — validates meter_id, current_reading (numeric, ≥ previous).

API requests reuse the same form requests; validation errors return JSON 422
automatically.
