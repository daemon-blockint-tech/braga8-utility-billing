# 01 — Getting Started

This document walks a new contributor from a clean checkout to a running
Braga8 Utility Billing application with seeded demo data.

## Prerequisites

| Tool | Minimum version | Notes |
| --- | --- | --- |
| PHP | 8.2 | Required by Laravel 12. Enable `pdo_sqlite`, `mbstring`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo` extensions. |
| Composer | 2.7+ | Dependency manager for PHP. |
| Node.js | 18+ | Required by Vite 6 for asset bundling. |
| npm | 9+ | Ships with Node.js. |
| SQLite | 3.35+ | Default dev database. Alternatively MySQL 8 or PostgreSQL 15. |
| Git | 2.30+ | For cloning and contributing. |

Optional but recommended:

- **Laravel Sail** — Docker-based local stack. See the

  [Laravel Sail docs](https://laravel.com/docs/12.x/sail) if you prefer
  containerized development.

- **Mailpit** or **Mailtrap** — For inspecting outbound email during

  reminder and notification testing.

## 1. Clone the repository

```bash
git clone <repo-url> braga8-utility-billing
cd braga8-utility-billing
```

## 2. Install PHP dependencies

```bash
composer install
```

This installs Laravel 12, Breeze, Sanctum, DomPDF, and Pest, along with
all other packages declared in `composer.json`.

## 3. Install frontend dependencies

```bash
npm install
```

This installs Vite, Tailwind CSS 4, PostCSS, Autoprefixer, Alpine.js 3,
and Axios.

## 4. Prepare the environment file

```bash
cp .env.example .env
php artisan key:generate
```

Review `.env` and adjust values for your local environment. Key settings
are documented in [05-configuration.md](05-configuration.md).

Minimum required values for a working dev environment:

```dotenv
APP_NAME="Braga8 Utility Billing"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_STORE=database

MAIL_MAILER=log
```

## 5. Run database migrations and seeders

```bash
php artisan migrate --seed
```

The seeder creates:

- The default admin user (`admin@braga8.test` / `password`).
- Sample tariffs, customers, meters, and invoices for manual testing.

See `database/seeders/DatabaseSeeder.php` for the full seed plan.

## 6. Build frontend assets

For development with hot module replacement:

```bash
npm run dev
```

For a production build:

```bash
npm run build
```

## 7. Start the application

```bash
php artisan serve
```

The application is now available at <http://localhost:8000>.

Log in with the seeded admin credentials shown above to access the
dashboard.

## 8. Start the queue worker (optional, for background jobs)

Reminder emails and PDF generation run on the queue. In a separate
terminal:

```bash
php artisan queue:work --tries=3
```

Without a running worker, queued jobs accumulate in the `jobs` table and
are processed on the next `queue:work` invocation. Use
`QUEUE_CONNECTION=sync` in `.env` to process jobs inline during early
development.

## 9. Run the scheduler (optional, for cron-driven commands)

The scheduled reminder commands run on Laravel's scheduler. For local
testing, start the scheduler in a separate terminal:

```bash
php artisan schedule:work
```

In production, register a single system cron entry:

```cron
* * * * * cd /path/to/braga8-utility-billing && php artisan schedule:run >> /dev/null 2>&1
```

See [03-commands-and-scheduling.md](03-commands-and-scheduling.md) for
the full list of scheduled commands.

## 10. Verify the installation

```bash
# Run the test suite
php artisan test

# Check code style
./vendor/bin/pint --test
```

A green test suite confirms the environment is correctly configured.

## Common Issues

### `SQLSTATE: no such table` after fresh clone

You skipped `php artisan migrate --seed`. Run it from the project root.

### `The MIX_ or VITE_ environment variable is required`

Ensure `npm run dev` (or `npm run build`) has been executed at least
once so `public/build/` exists.

### `Class "App\Models\User" not found` after editing models

Run `composer dump-autoload` to refresh the optimized class map.

### Reminder emails are not being sent

1. Confirm `MAIL_MAILER` is set to a working mailer (`log`, `smtp`,

   `mailgun`, etc.).

2. Confirm a queue worker is running (`php artisan queue:work`) or

   `QUEUE_CONNECTION=sync`.

3. Check the `failed_jobs` table with `php artisan queue:failed`.

## Next Steps

- Read [02-project-structure.md](02-project-structure.md) to learn where

  code lives.

- Read [03-commands-and-scheduling.md](03-commands-and-scheduling.md) to

  understand the background jobs.

- Read [06-testing.md](06-testing.md) to start contributing tests.
