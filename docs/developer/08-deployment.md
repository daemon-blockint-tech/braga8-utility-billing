# 08 — Deployment

This document covers deploying the Braga8 Utility Billing application
to production, including server requirements, build steps, zero-downtime
strategies, and post-deployment verification.

## Server Requirements

- **PHP** 8.2 or higher with extensions: `ctype`, `curl`, `dom`, `fileinfo`,

  `filter`, `hash`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `tokenizer`,
  `xml`, `gd` (for image handling).

- **MySQL** 8.0+ or MariaDB 10.6+.
- **Redis** 6+ (recommended for cache, session, queue).
- **Nginx** or **Apache** with URL rewriting enabled.
- **Composer** 2.x.
- **Node.js** 18+ and **npm** for building frontend assets (only needed

  during the build step, not on the runtime server).

- **Supervisor** for queue workers (production).
- **HTTPS** via Let's Encrypt or a managed certificate.

## Production Environment Checklist

Before deploying, verify the following in `.env`:

- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_URL=https://your-domain.com`
- [ ] `APP_KEY` set and kept secret
- [ ] `DB_*` pointing to the production database
- [ ] `CACHE_STORE=redis`
- [ ] `SESSION_DRIVER=redis`
- [ ] `QUEUE_CONNECTION=redis`
- [ ] `MAIL_MAILER=smtp` with valid production SMTP credentials
- [ ] `FILESYSTEM_DISK=s3` (or persistent local storage with backups)
- [ ] `SANCTUM_STATEFUL_DOMAINS` includes the frontend domain

## Build & Deploy Steps

### 1. Build frontend assets

Run on a build machine or CI runner (not the production server, to keep
Node off the runtime):

```bash
npm ci
npm run build
```

This produces `public/build/` with hashed, minified assets.

### 2. Install PHP dependencies

```bash
composer install --no-dev --optimize-autoloader
```

`--no-dev` excludes test-only packages, reducing the deploy footprint.

### 3. Upload code and assets

Transfer the application code (excluding `vendor/`, `node_modules/`,
`.env`, and `storage/` logs) to the server. Common approaches:

- **Git pull** on the server (simplest, requires Git on the server).
- **rsync** from CI to the server.
- **Deployer** or **Envoyer** for atomic releases with rollbacks.

### 4. Run migrations

```bash
php artisan migrate --force
```

`--force` skips the confirmation prompt in non-interactive environments.

### 5. Cache configuration and routes

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

> **Important:** Run `config:cache` **after** setting the correct
> `.env` values. Caching freezes the current env values into a single
> PHP array for performance.

### 6. Link storage

```bash
php artisan storage:link
```

Creates the `public/storage` symlink to `storage/app/public`.

### 7. Set permissions

Web server (e.g. `www-data`) must own `storage/` and `bootstrap/cache/`:

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### 8. Restart queue workers

After each deploy, restart workers so they pick up new code:

```bash
php artisan queue:restart
```

Supervisor will automatically restart the worker processes.

### 9. Reload PHP-FPM (if using OPcache)

```bash
sudo systemctl reload php8.2-fpm
```

Or clear OPcache via the admin endpoint if using a long-running process.

## Web Server Configuration

### Nginx

```nginx
server {
    listen 80;
    server_name braga8.example.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name braga8.example.com;

    ssl_certificate     /etc/letsencrypt/live/braga8.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/braga8.example.com/privkey.pem;

    root /var/www/braga8/current/public;
    index index.php;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Queue Workers with Supervisor

Create `/etc/supervisor/conf.d/braga8-worker.conf`:

```ini
[program:braga8-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/braga8/current/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/braga8/current/storage/logs/worker.log
stopwaitsecs=3600
```

Apply:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start braga8-worker:*
```

Tune `numprocs` based on available CPU and memory. Each worker holds
one PHP process in memory.

## Scheduler

Add a single cron entry to run Laravel's scheduler every minute:

```cron
* * * * * cd /var/www/braga8/current && php artisan schedule:run >> /dev/null 2>&1
```

The scheduler dispatches `SendReminders`, billing generation, and other
scheduled commands defined in `routes/console.php` and
`app/Console/Kernel.php`.

## Zero-Downtime Deployment

### Atomic releases with symlinks

Maintain a `releases/` directory and a `current` symlink:

```text
/var/www/braga8/
├── releases/
│   ├── 20260401-120000/
│   ├── 20260402-150000/
│   └── 20260403-100000/   ← newest
└── current -> releases/20260403-100000
```

Deploy steps:

1. Build and upload new release to `releases/<timestamp>/`.
2. Run migrations and `optimize` commands inside the new release.
3. Symlink `storage/` from a shared location.
4. Flip `current` symlink to the new release.
5. Reload PHP-FPM and restart queue workers.
6. Remove old releases (keep last 3-5 for rollback).

### Rollback

```bash
ln -sfn /var/www/braga8/releases/20260402-150000 /var/www/braga8/current
sudo systemctl reload php8.2-fpm
php artisan queue:restart
```

> If a migration is not reversible, rollback may require manual database
> intervention. Always test migrations on staging first.

## Post-Deployment Verification

After deploying, verify:

1. **Health check:** `curl -I https://braga8.example.com` returns `200`.
2. **Login:** Log in as a test user.
3. **Queue:** `php artisan queue:work --once` runs without error.
4. **Scheduler:** `php artisan schedule:list` shows expected jobs.
5. **Logs:** `tail -f storage/logs/laravel.log` shows no new errors.
6. **Database:** `php artisan migrate:status` shows all migrations

   applied.

7. **Cache:** `php artisan tinker --execute="cache(['test' => 1], 1);"`

   succeeds.

## Monitoring

- **Application logs:** `storage/logs/laravel.log` (rotate via

  `laravel.log` daily rotation in `config/logging.php`).

- **Server monitoring:** Uptime checks (UptimeRobot, Pingdom), CPU and

  memory alerts (Prometheus + Grafana or server provider monitoring).

- **Error tracking:** Sentry or Bugsnag integration (add DSN to `.env`).
- **Database monitoring:** Slow query log, connection pool usage.

## Backups

- **Database:** Daily `mysqldump` or managed RDS snapshots, retained

  for at least 30 days. Test restores quarterly.

- **Storage:** If using local disk for PDFs, back up `storage/app/`

  nightly. If using S3, enable versioning and cross-region replication.

- **Configuration:** Keep `.env` backed up in a secrets manager, not in

  the repository.

## Maintenance Mode

To temporarily take the app offline for major changes:

```bash
php artisan down --message="Upgrading billing system" --retry=60
```

Visitors see a 503 maintenance page. Exempt specific IPs:

```bash
php artisan down --secret="maintenance-token"
```

Then access `https://braga8.example.com/maintenance-token` to bypass.

Bring the app back up:

```bash
php artisan up
```
