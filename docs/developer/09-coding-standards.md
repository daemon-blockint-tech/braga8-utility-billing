# Coding Standards

This document defines the conventions and quality expectations for contributors working on the Braga8 Utility Billing application. The standards are derived from the existing codebase patterns and Laravel community best practices.

---

## 1. PHP & Framework

| Aspect | Standard |
| --- | --- |
| PHP version | 8.2+ (enforced by `composer.json`) |
| Framework | Laravel 12.x |
| Style fixer | Laravel Pint (PSR-12 + Laravel preset) |
| Static analysis | (Optional) PHPStan / Larastan — recommended for new modules |
| Test framework | Pest PHP 4.x with `pest-plugin-laravel` |

### Running the formatter

```bash
# Check only (CI)
vendor/bin/pint --test

# Auto-fix
vendor/bin/pint
```

Always run Pint before committing. CI is expected to fail on style violations.

---

## 2. Naming Conventions

| Element | Convention | Example |
| --- | --- | --- |
| Class (Model) | Singular, StudlyCase | `UtilityMeter`, `MeterReading` |
| Class (Controller) | StudlyCase + `Controller` suffix | `InvoiceController` |
| Class (Policy) | StudlyCase + `Policy` suffix | `InvoicePolicy` |
| Class (Command) | StudlyCase + verb | `SendReminder` |
| Method | camelCase | `generateInvoiceNumber()` |
| Variable | camelCase | `$billingPeriod` |
| Property (Eloquent) | snake_case in DB, camelCase in PHP | `total_amount` / `$invoice->total_amount` |
| Database table | snake_case, plural | `meter_readings`, `utility_meters` |
| Migration | `YYYY_MM_DD_HHMMSS_snake_case_description` | `2026_03_31_162328_add_name_to_tariffs_table` |
| Route name | snake_case.dot | `invoices.index`, `invoices.show` |
| View file | snake_case.blade.php | `invoices/index.blade.php` |
| Config key | snake_case | `billing.default_currency` |
| Test function | snake_case description | `test_profile_page_is_displayed` |

---

## 3. Models

Every Eloquent model in Braga8 follows the same skeleton:

```php
<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use LogsActivity;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'unit_id',
        'invoice_number',
        'billing_period_start',
        'billing_period_end',
        'total_amount',
        'notified_at',
        'status',
    ];

    protected $casts = [
        'notified_at'         => 'datetime',
        'billing_period_start' => 'date',
        'billing_period_end'   => 'date',
    ];

    // Relationships
    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function unit(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
```

### Rules

1. **Always declare `$fillable`**. Never use `$guarded = []` — it bypasses mass-assignment protection.
2. **Always declare `$casts`** for date, boolean, JSON, and enum columns.
3. **Use the `LogsActivity` trait** on any model whose changes must appear in the audit log (`audit_logs` table). The trait auto-writes `action`, `model`, `model_id`, `before`, `after`, and `user_id`.
4. **Relationships return typed relations** (`BelongsTo`, `HasMany`, etc.) so IDEs and static analyzers can resolve them.
5. **No business logic in models** beyond simple scopes, accessors, and mutators. Put calculations in services or actions.
6. **Factories**: every model with `HasFactory` must have a matching factory in `database/factories/`.

---

## 4. Controllers

### 4.1 Structure

```php
<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['tenant', 'unit']);

        // Filtering / search
        if ($request->filled('search')) {
            // ... build query conditions
        }

        $invoices = $query->latest()->paginate(10)->appends($request->all());

        return view('invoices.index', compact('invoices'));
    }
}
```

### 4.2 Rules

1. **Resourceful methods** (`index`, `create`, `store`, `show`, `edit`, `update`, `destroy`) are preferred for CRUD resources.
2. **Eager-load relationships** with `with([...])` to avoid N+1 queries, especially in list views.
3. **Use `paginate()`** for list endpoints — never `->get()` on unbounded collections.
4. **Validate input in the controller** using `$request->validate([...])` or a Form Request for complex rules.
5. **Redirect after state changes**: `return redirect()->route('invoices.index')->with('status', '...')`.
6. **Authorization**: use `authorize()` or policy methods before mutating resources. See §6.
7. **No direct DB::raw in controllers** — move complex queries to a query scope or service class.
8. **Indonesian month search** (e.g. `januari`, `februari`) is an accepted domain convention in `InvoiceController::index`; preserve it when extending search.

---

## 5. Routes

```php
// routes/web.php
use App\Http\Controllers\InvoiceController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('invoices', InvoiceController::class);
    Route::patch('invoices/{invoice}/pay', [InvoiceController::class, 'markPaid'])
        ->name('invoices.pay');
});
```

### Routing Rules

1. **Group by middleware** — `auth`, `verified`, `role:admin` etc.
2. **Name every route** — `->name('resource.action')`. Never hard-code URLs in views; always use `route()`.
3. **Use `Route::resource`** for standard CRUD. Add custom actions as separate named routes.
4. **Role middleware**: `role:admin|manager` delegates to `App\Http\Middleware\CheckRole`.
5. **API routes** (if added) live in `routes/api.php` and use Sanctum tokens.

---

## 6. Authorization

### 6.1 Role middleware

The `CheckRole` middleware accepts a pipe-separated list of allowed roles:

```php
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class);
});
```

### 6.2 Policies

Create a policy per model when fine-grained checks are needed:

```bash
php artisan make:policy InvoicePolicy --model=Invoice
```

Policies are auto-discovered by Laravel. Use them in controllers:

```php
$this->authorize('update', $invoice);
```

### 6.3 Roles

Roles are stored as a string column on `users.role`. Current values:

- `admin` — full access
- `manager` — operational access, no user management
- `tenant` — end-user, sees only own data

Never compare roles with raw string equality in views — use `Auth::user()->role === 'admin'` only inside policies or middleware, and prefer a `User::isAdmin()` helper method when reused.

---

## 7. Audit Logging

The `App\Traits\LogsActivity` trait hooks into Eloquent `creating`, `updating`, and `deleting` events and writes a row to `audit_logs`:

| Column | Source |
| --- | --- |
| `user_id` | `Auth::id()` (nullable on console commands) |
| `action` | `create` / `update` / `delete` |
| `model` | class basename |
| `model_id` | model primary key |
| `before` | JSON snapshot prior to change |
| `after` | JSON snapshot after change |

**Rules**:

- Apply the trait to any model that represents business state (`Invoice`, `Payment`, `Tenant`, `Unit`, `UtilityMeter`, `Tariff`, `MeterReading`, `User`, `Reminder`, `Complaint`).
- Do **not** apply it to log-only or pivot models.
- Console commands that mutate audited models should set `Auth::loginUsingId(...)` if a user context is available, otherwise `user_id` will be null (acceptable for system jobs).

---

## 8. Migrations

1. **One concern per migration** — do not combine schema changes with data backfills in the same file unless unavoidable.
2. **Always define `up()` and `down()`** — migrations must be reversible.
3. **Use `->nullable()`** for columns added to existing tables with data.
4. **Foreign keys**: use `unsignedBigInteger` + `foreignId(...)->constrained()->cascadeOnDelete()` for Laravel 12 conventions.
5. **Timestamps**: every business table has `created_at` and `updated_at` unless it is a pure pivot.
6. **Naming**: `YYYY_MM_DD_HHMMSS_description_table.php` — generated by `php artisan make:migration`.

---

## 9. Blade Views

1. **Layout inheritance**: `@extends('layouts.app')` for authenticated pages, `@extends('layouts.commercial')` for the public landing.
2. **Sections**: `@section('content') ... @endsection`.
3. **Never inline SQL or business logic** in Blade — pass everything from the controller.
4. **Use `route()` helper** for all links and form actions.
5. **CSRF**: every POST/PATCH/DELETE form must include `@csrf`.
6. **Localization**: the UI is bilingual (Indonesian primary, English secondary). Keep strings in Blade; if i18n grows, migrate to `lang/id/*.php` files.
7. **Conditional rendering for roles**:

   ```blade
   @if(auth()->user()->role === 'admin')
       <a href="{{ route('users.index') }}">Manajemen Pengguna</a>
   @endif
   ```

---

## 10. Testing

### 10.1 Framework

Tests use **Pest PHP** with the Laravel plugin. Configuration lives in `phpunit.xml` and `tests/Pest.php`.

### 10.2 Test structure

```php
<?php

use App\Models\User;
use App\Models\Invoice;

test('admin can view invoices index', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('invoices.index'))
        ->assertOk();
});

test('tenant cannot access invoices index', function () {
    $tenant = User::factory()->create(['role' => 'tenant']);

    $this->actingAs($tenant)
        ->get(route('invoices.index'))
        ->assertForbidden();
});
```

### 10.3 Rules

1. **Test naming**: `test('description in present tense', function () { ... });`.
2. **Use factories** — never insert raw DB rows in tests. Every model with `HasFactory` has a factory.
3. **Refresh database**: `tests/TestCase` uses `RefreshDatabase` by default for feature tests.
4. **Test layers**:
   - `tests/Unit/` — pure PHP logic, helpers, service classes.
   - `tests/Feature/` — HTTP endpoints, auth flows, model persistence.
5. **Assertions over expectations**: prefer `assertOk()`, `assertRedirect()`, `assertSessionHasNoErrors()` over manual `assertEquals(200, ...)`.
6. **Role coverage**: every protected route should have at least one positive and one negative role test.
7. **Run tests**:

   ```bash
   php artisan test              # all
   php artisan test --parallel   # faster on multi-core
   php artisan test --filter=Invoice
   ```

---

## 11. Console Commands

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reminder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendReminder extends Command
{
    protected $signature = 'reminders:send';
    protected $description = 'Send reminders based on reminder_date';

    public function handle(): int
    {
        // ... logic
        $this->info('Reminders processed.');
        return self::SUCCESS;
    }
}
```

### Command Rules

1. **`$signature`** uses `namespace:action` form (`reminders:send`, `invoices:generate`).
2. **`$description`** is a human-readable sentence.
3. **`handle()` returns `int`** — `self::SUCCESS` or `self::FAILURE`.
4. **Log meaningful events** with `Log::info()` / `Log::error()` for observability.
5. **Idempotency**: commands that run on a schedule must be safe to re-run.
6. **Register in `routes/console.php` or `app/Console/Kernel.php`** as scheduled tasks with `->daily()`, `->weekly()`, etc.

---

## 12. Scheduled Tasks

Defined in `routes/console.php` (Laravel 12 convention):

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('reminders:send')->dailyAt('08:00');
Schedule::command('invoices:generate')->monthlyOn(1, '00:30');
```

- Use descriptive cron expressions or fluent helpers.
- Set `->withoutOverlapping()` for long-running jobs.
- Set `->runInBackground()` when the task does not block subsequent ones.

---

## 13. Security Conventions

1. **Never trust user input** — validate every request.
2. **Never log secrets** — passwords, tokens, API keys. Pint + Semgrep rules enforce this.
3. **Mass assignment**: `$fillable` only, never `$guarded = []`.
4. **SQL injection**: use Eloquent or query builder parameter binding. No raw concatenation.
5. **XSS**: Blade `{{ }}` escapes by default. Use `{!! !!}` only for trusted HTML.
6. **CSRF**: `@csrf` on every state-changing form.
7. **File uploads**: validate mime, size, and store outside `public/` when sensitive.
8. **Auth checks**: every route that mutates data must pass through `auth` + role middleware.
9. **Custom Semgrep rules** in `.semgrep/braga8-custom.yml` catch project-specific anti-patterns. Run before push:

   ```bash
   semgrep --config .semgrep/braga8-custom.yml
   ```

---

## 14. Git Workflow

### 14.1 Branch naming

```text
feature/<short-description>      # new functionality
fix/<short-description>          # bug fix
chore/<short-description>        # tooling, deps, docs
hotfix/<short-description>       # urgent production fix
```

### 14.2 Commit messages

Follow Conventional Commits:

```text
feat(invoices): add PDF export endpoint
fix(meters): correct reading validation for negative values
chore(deps): bump laravel/framework to 12.x
docs(prd): update non-functional requirements
```

### 14.3 Pull requests

- One concern per PR.
- Include a description, test plan, and screenshots for UI changes.
- CI must pass: Pint, Pest, Semgrep.
- Require at least one reviewer for `main` merges.

---

## 15. Dependency Management

1. **PHP deps**: `composer require <pkg>` — prefer stable releases, avoid `dev-master`.
2. **JS deps**: `npm install <pkg>` — pin to a minor range, never `latest`.
3. **New packages** require a justification in the PR description.
4. **Security advisories**: run `composer audit` and `npm audit` weekly.
5. **Never commit `composer.lock` or `package-lock.json` changes** that come from a local PHP/Node version mismatch — regenerate in CI.

---

## 16. Environment & Configuration

1. **`.env` is never committed**. `.env.example` is the source of truth for required keys.
2. **Config keys** live in `config/*.php` and read via `config('file.key')`.
3. **No secrets in config files** — always `env('KEY')` with a safe default.
4. **Per-environment overrides**: `config/billing.php` etc. should support `production`, `staging`, `local` via env vars.

---

## 17. Performance Notes

1. **Eager load** relationships in list views (`with()`).
2. **Paginate** — default 10–25 per page.
3. **Cache expensive reads** with `Cache::remember('key', 300, fn () => ...)` when data is stable.
4. **Queue heavy work** — PDF generation, email sending, report exports should be queued, not inline.
5. **Avoid `->get()` then `->filter()`** — push conditions into the query.

---

## 18. Internationalization

The application UI is primarily in **Bahasa Indonesia** with English fallbacks.

- Keep user-facing strings in Blade as-is for now.
- If i18n is formalized, move strings to `lang/id/messages.php` and `lang/en/messages.php`, then use `__('messages.key')`.
- Date formatting uses Carbon with `->locale('id')` for Indonesian month names.

---

## 19. Code Review Checklist

Before requesting review, confirm:

- [ ] Pint passes (`vendor/bin/pint --test`)
- [ ] Tests pass (`php artisan test`)
- [ ] New code has tests
- [ ] No `dd()` / `dump()` left in code
- [ ] No hardcoded URLs, strings, or credentials
- [ ] `$fillable` declared on any new model
- [ ] `LogsActivity` trait applied to business models
- [ ] Routes named and middleware-protected
- [ ] Migration has reversible `down()`
- [ ] Semgrep clean (`semgrep --config .semgrep/braga8-custom.yml`)
- [ ] PR description explains the *why*, not just the *what*

---

## 20. References

- [Laravel 12 Documentation](https://laravel.com/docs/12.x)
- [Pest PHP](https://pestphp.com)
- [Laravel Pint](https://laravel.com/docs/12.x/pint)
- [PSR-12](https://www.php-fig.org/psr/psr-12/)
- [Conventional Commits](https://www.conventionalcommits.org)
- Internal: `SECURITY_AUDIT_REPORT.md`, `.semgrep/braga8-custom.yml`
