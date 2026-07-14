# 02 — Project Structure

This document describes the directory layout of the Braga8 Utility Billing
application and explains where each kind of code lives.

## Top-level layout

```text
braga8-utility-billing/
├── app/                  # Application code (PHP)
├── bootstrap/            # Framework bootstrap and app configuration
├── config/               # Configuration files
├── database/             # Migrations, seeders, and factories
├── docs/                 # Project documentation
├── lang/                 # Localization language files
├── public/               # Document root (index.php, built assets)
├── resources/            # Views, CSS, JS, and lang files
├── routes/               # HTTP and console route definitions
├── storage/              # Logs, cache, uploads, framework state
├── tests/                # Pest test suite
├── .env.example          # Example environment configuration
├── composer.json         # PHP dependencies and scripts
├── package.json          # Node dependencies and build scripts
├── phpunit.xml           # Test runner configuration
├── pint.json             # Laravel Pint code style configuration
├── vite.config.js        # Vite asset bundler configuration
└── README.md             # Project README
```

## `app/`

The application's PHP source code, organized by Laravel convention.

```text
app/
├── Console/
│   └── Commands/         # Artisan commands (SendReminder, etc.)
├── Http/
│   ├── Controllers/      # HTTP request handlers
│   ├── Middleware/       # Request middleware (auth, admin, etc.)
│   ├── Requests/         # Form request validation classes
│   └── Resources/        # API response resource classes
├── Models/               # Eloquent ORM models
├── Notifications/        # Email / database notifications
├── Policies/             # Authorization policy classes
├── Providers/            # Service providers
├── Services/             # Domain service classes (billing, PDF, etc.)
└── Mail/                 # Mailable classes (if any)
```

### Conventions

- **One model per file**, named in singular form (`Customer`, `Invoice`).
- **Controllers** are grouped by domain (`CustomerController`,

  `InvoiceController`) and use resource methods where possible.

- **Form Requests** validate input at the controller boundary; controllers

  stay thin.

- **Policies** gate authorization and are auto-discovered by Laravel.
- **Services** encapsulate business logic that does not belong in a model

  or controller (e.g. `BillingService`, `PdfService`).

## `bootstrap/`

```text
bootstrap/
├── app.php               # Application bootstrap: aliases, middleware, exceptions
└── cache/                # Cached bootstrap files (auto-generated)
```

`bootstrap/app.php` registers middleware aliases (`admin`, `verified`,
etc.) and configures exception handling. See
[04-routes-and-controllers.md](04-routes-and-controllers.md) for the
middleware stack.

## `config/`

Configuration files returned as PHP arrays. Each file corresponds to a
configuration namespace accessed via `config('namespace.key')`.

Key files:

| File | Purpose |
| --- | --- |
| `app.php` | Application name, env, debug, timezone, locale, providers, aliases |
| `auth.php` | Guards and providers (web, sanctum) |
| `database.php` | Default connection and connection definitions |
| `filesystems.php` | Disk configurations (local, public, s3) |
| `mail.php` | Mailer configurations |
| `queue.php` | Queue connections (sync, database, redis) |
| `sanctum.php` | Sanctum stateful domains and expiration |
| `services.php` | Third-party service credentials |
| `session.php` | Session driver and lifetime |

See [05-configuration.md](05-configuration.md) for details.

## `database/`

```text
database/
├── factories/            # Model factories for testing
├── migrations/           # Schema migrations (timestamped)
└── seeders/              # Database seeders
```

- **Migrations** are timestamped and ordered. Never edit a merged

  migration; create a new one to alter the schema.

- **Factories** define default attribute sets for each model, used by

  tests and seeders.

- **Seeders** populate the database with demo data. `DatabaseSeeder`

  orchestrates the others.

## `docs/`

Project documentation, organized into four sections:

```text
docs/
├── PRD/                  # Product Requirements Document
├── architecture/         # System architecture
├── developer/            # This guide
└── user/                 # End-user guide
```

## `resources/`

```text
resources/
├── css/                  # Tailwind CSS entry (app.css)
├── js/                   # Alpine.js modules and bootstrap.js
└── views/                # Blade templates
```

Views follow Laravel's dotted namespace convention. For example,
`resources/views/customers/index.blade.php` is referenced as
`view('customers.index')`.

## `routes/`

```text
routes/
├── console.php           # Console command definitions and schedule
├── web.php               # Web (browser) routes
└── api.php               # API routes (if enabled, Sanctum-protected)
```

See [04-routes-and-controllers.md](04-routes-and-controllers.md) for the
full route map.

## `storage/`

```text
storage/
├── app/                  # Application-generated files (uploads, exports)
├── framework/            # Framework-managed cache, sessions, views
├── logs/                 # Application log files (laravel.log)
└── pdf/                  # Generated PDF invoices (if disk-based)
```

The `storage/app/public` directory is symlinked to `public/storage` via
`php artisan storage:link`. Uploaded files served to the browser must
live under this path.

## `tests/`

```text
tests/
├── Feature/              # Feature tests (HTTP, queue, console)
├── Unit/                 # Unit tests (isolated classes)
├── Pest.php              # Pest bootstrap and global helpers
└── TestCase.php          # Base test class
```

See [06-testing.md](06-testing.md) for the testing strategy.

## Naming Conventions

| Artifact | Convention | Example |
| --- | --- | --- |
| Model | Singular, PascalCase | `Customer`, `InvoiceItem` |
| Migration | `YYYY_MM_DD_HHMMSS_snake_case_action_table` | `2026_03_31_162328_add_name_to_tariffs_table` |
| Controller | PascalCase + `Controller` suffix | `InvoiceController` |
| Form Request | PascalCase + `Request` suffix | `StoreInvoiceRequest` |
| Policy | PascalCase + `Policy` suffix | `InvoicePolicy` |
| Artisan command | PascalCase + descriptive verb | `SendReminder` |
| Blade view | snake_case, dotted namespace | `customers.partials.form` |
| Config key | snake_case | `mail.default` |
| Route name | dotted, snake_case | `customers.index` |
| Test method | snake_case | `it_can_create_a_customer` |

## Where to Put New Code

| You are adding... | Put it in... |
| --- | --- |
| A new DB table | `database/migrations/` |
| A new entity | `app/Models/` + migration + factory + policy |
| A new page | `resources/views/<domain>/` + route in `routes/web.php` + controller method |
| A new API endpoint | `routes/api.php` + controller + `app/Http/Resources/` |
| A new background job | `app/Console/Commands/` + schedule entry in `routes/console.php` |
| A new notification | `app/Notifications/` |
| A new validation rule | `app/Http/Requests/` (form request) or `app/Rules/` (custom rule) |
| A new service | `app/Services/` |
| A new test | `tests/Feature/<Domain>/` or `tests/Unit/` |
