# 05 — Configuration

This document explains how the Braga8 Utility Billing application is
configured across environments, covering environment variables, config
files, and environment-specific overrides.

## Environment Variables (`.env`)

The `.env` file is the primary source of environment-specific
configuration. It is **not** committed to version control. Copy
`.env.example` to `.env` and adjust values for your environment.

```bash
cp .env.example .env
php artisan key:generate
```

### Core application variables

| Variable | Default | Description |
| --- | --- | --- |
| `APP_NAME` | `Braga8 Utility Billing` | Application name |
| `APP_ENV` | `local` | `local`, `staging`, `production` |
| `APP_KEY` | (generated) | Encryption key; generate with `key:generate` |
| `APP_DEBUG` | `true` | Show detailed errors; **must be `false` in production** |
| `APP_URL` | `http://localhost` | Canonical application URL |
| `APP_TIMEZONE` | `UTC` | Application timezone |
| `APP_LOCALE` | `en` | Default UI locale |

### Database

| Variable | Default | Description |
| --- | --- | --- |
| `DB_CONNECTION` | `mysql` | Default connection name |
| `DB_HOST` | `127.0.0.1` | Database host |
| `DB_PORT` | `3306` | Database port |
| `DB_DATABASE` | `braga8` | Database name |
| `DB_USERNAME` | `root` | Database user |
| `DB_PASSWORD` | (empty) | Database password |

### Mail

| Variable | Default | Description |
| --- | --- | --- |
| `MAIL_MAILER` | `smtp` | Default mailer |
| `MAIL_HOST` | `smtp.mailtrap.io` | SMTP host |
| `MAIL_PORT` | `2525` | SMTP port |
| `MAIL_USERNAME` | (empty) | SMTP user |
| `MAIL_PASSWORD` | (empty) | SMTP password |
| `MAIL_ENCRYPTION` | `tls` | `tls`, `ssl`, or null |
| `MAIL_FROM_ADDRESS` | `noreply@braga8.test` | From address |
| `MAIL_FROM_NAME` | `${APP_NAME}` | From name |

### Queue

| Variable | Default | Description |
| --- | --- | --- |
| `QUEUE_CONNECTION` | `database` | `sync`, `database`, `redis`, `sqs` |
| `QUEUE_FAILED_DRIVER` | `database-uuids` | Failed job storage |

### Session & Cache

| Variable | Default | Description |
| --- | --- | --- |
| `SESSION_DRIVER` | `database` | `file`, `database`, `redis`, `cookie` |
| `SESSION_LIFETIME` | `120` | Session lifetime in minutes |
| `CACHE_STORE` | `database` | `file`, `database`, `redis`, `array` |

### Filesystem

| Variable | Default | Description |
| --- | --- | --- |
| `FILESYSTEM_DISK` | `local` | Default disk for file operations |
| `AWS_ACCESS_KEY_ID` | (empty) | S3 credentials (if using S3) |
| `AWS_SECRET_ACCESS_KEY` | (empty) | S3 secret |
| `AWS_DEFAULT_REGION` | `us-east-1` | S3 region |
| `AWS_BUCKET` | (empty) | S3 bucket name |

### Sanctum / API

| Variable | Default | Description |
| --- | --- | --- |
| `SANCTUM_STATEFUL_DOMAINS` | `localhost,localhost:5173,127.0.0.1,127.0.0.1:8000` | Stateful domains for SPA auth |
| `SESSION_DOMAIN` | (empty) | Session cookie domain |

## Config Files (`config/`)

Each file in `config/` returns a PHP array. Values are read via
`config('file.key')` and can be overridden by environment variables using
Laravel's `env()` helper.

### `config/app.php`

Application-wide settings: name, environment, debug mode, URL, timezone,
locale, fallback locale, Faker locale, encryption key, provider list,
and alias list.

### `config/auth.php`

Defines authentication guards and providers.

- **`web` guard** — session + encrypted cookie, used by browser routes.
- **`sanctum` guard** — token-based, used by API routes.
- **User provider** — Eloquent provider backed by `App\Models\User`.

### `config/database.php`

Default connection and connection definitions for MySQL, PostgreSQL,
SQLite, and SQL Server. The `migrations` table name and Redis client
are also configured here.

### `config/filesystems.php`

Disk definitions:

- **`local`** — `storage/app/private`, not web-accessible.
- **`public`** — `storage/app/public`, web-accessible via

  `public/storage` symlink.

- **`s3`** — S3-compatible object storage, used in production.

Generated PDF invoices are written to the `local` disk by default and
served through a controller action (not directly from the public disk)
to enforce authorization.

### `config/mail.php`

Mailer definitions. The default mailer is `smtp`. Additional mailers
(`log`, `array`, `ses`) are available for testing and production.

### `config/queue.php`

Queue connections. In development `sync` is convenient (jobs run
inline); in production use `database` or `redis`. Failed jobs are stored
in the `failed_jobs` table.

### `config/sanctum.php`

Sanctum configuration for API token authentication. Stateful domains
must include the frontend origin for SPA cookie authentication.

### `config/services.php`

Third-party service credentials. Add new integrations here.

## Environment-Specific Configuration

### Local development

- `APP_ENV=local`, `APP_DEBUG=true`
- `QUEUE_CONNECTION=sync` (or `database` if testing workers)
- `MAIL_MAILER=log` (emails written to `storage/logs/laravel.log`)
- `CACHE_STORE=array` or `database`
- SQLite or local MySQL

### Staging

- `APP_ENV=staging`, `APP_DEBUG=false`
- `QUEUE_CONNECTION=database` or `redis`
- `MAIL_MAILER=smtp` (Mailtrap or staging SMTP)
- `CACHE_STORE=database` or `redis`
- Separate database from production

### Production

- `APP_ENV=production`, `APP_DEBUG=false`
- `QUEUE_CONNECTION=redis` (recommended)
- `MAIL_MAILER=smtp` (production SMTP relay or SES)
- `CACHE_STORE=redis`
- `FILESYSTEM_DISK=s3` (recommended for PDFs and uploads)
- `SESSION_DRIVER=redis`
- TLS termination at reverse proxy; `APP_URL` uses `https://`

## Caching Configuration

After deploying or changing config, cache it for performance:

```bash
php artisan config:cache
```

**Never** run `config:cache` in development — it prevents `.env`
changes from taking effect until you clear the cache.

To clear:

```bash
php artisan config:clear
# or clear everything:
php artisan optimize:clear
```

## Adding a New Configuration Value

1. Add the variable to `.env.example` with a sensible default.
2. Read it in the relevant `config/*.php` file via `env('KEY', 'default')`.
3. Access it in code via `config('file.key')`.
4. Document it in this file.
5. Add it to the deployment checklist for production.

## Security Notes

- **Never commit `.env`** to version control. It is in `.gitignore`.
- **Rotate `APP_KEY`** carefully; changing it invalidates all encrypted

  cookies and encrypted columns. Plan a maintenance window.

- **Restrict file permissions** on `.env` (e.g. `chmod 600 .env`).
- **Use secrets management** (Vault, AWS Secrets Manager, etc.) in

  production rather than editing `.env` directly on the server.
