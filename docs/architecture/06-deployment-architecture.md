# 06 — Deployment Architecture

## 1. Target Environment

| Aspect | Value |
| -------- | ------- |
| OS | Linux (Ubuntu 22.04 LTS or equivalent). |
| Web server | Nginx reverse proxy → PHP-FPM. |
| PHP | 8.2+ with extensions: `mbstring`, `xml`, `bcmath`, `gd`, `pdo-mysql`, `zip`. |
| Database | MySQL 8.0 / MariaDB 10.6+ (InnoDB). |
| App server | Laravel 12 (no Octane; standard PHP-FPM request lifecycle). |
| Queue | None — synchronous execution. Scheduler runs via cron. |
| Cache | `database` driver (cache table). Switch to Redis in production. |
| Session | `database` driver. |
| File storage | Local disk (`storage/app`). |

## 2. Directory Layout (Production)

```text
/var/www/braga8/
├── app/                      # Application code
├── bootstrap/
├── config/
├── database/
├── public/                   # Document root
│   ├── build/                # Vite-compiled assets (hashed)
│   └── index.php             # Front controller
├── resources/
├── routes/
├── storage/
│   ├── app/
│   │   └── public/           # Symlinked into public/storage
│   │       ├── invoices/     # Generated PDFs
│   │       └── payments/     # Uploaded proof images
│   ├── framework/
│   └── logs/                 # laravel.log
├── vendor/
└── .env                      # Environment config (not in git)
```

Nginx `root` points to `/var/www/braga8/public`.

## 3. Nginx Configuration (Reference)

```nginx
server {
    listen 80;
    server_name braga8.example.com;
    root /var/www/braga8/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Deny dotfiles
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Static asset caching
    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff2?)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

## 4. Environment Variables (`.env`)

| Key | Purpose | Production guidance |
| ----- | --------- | --------------------- |
| `APP_ENV` | Environment name. | `production`. |
| `APP_DEBUG` | Verbose errors. | **`false`** in production. |
| `APP_URL` | Canonical URL. | `https://braga8.example.com`. |
| `APP_KEY` | Encryption key. | `php artisan key:generate`, rotate on compromise. |
| `DB_CONNECTION` | DB driver. | `mysql`. |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | DB credentials. | Use a dedicated least-privilege MySQL user. |
| `FILESYSTEM_DISK` | Default disk. | `public` (local + symlink). |
| `SESSION_DRIVER` | Session store. | `database`. |
| `CACHE_STORE` | Cache store. | `database` (or `redis`). |
| `QUEUE_CONNECTION` | Queue driver. | `sync` (no background workers). |
| `MAIL_*` | SMTP for password reset. | Configure real SMTP relay. |
| `SANCTUM_STATEFUL_DOMAINS` | Sanctum stateful domains. | Not used for token API; leave empty. |

## 5. Deployment Steps

Standard Laravel deploy:

```bash
# 1. Pull code
cd /var/www/braga8
git pull origin main

# 2. Install PHP deps (no dev)
composer install --no-dev --optimize-autoloader

# 3. Install/build frontend
npm ci
npm run build

# 4. Migrate
php artisan migrate --force

# 5. Cache config + routes + views
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Symlink storage
php artisan storage:link

# 7. Restart PHP-FPM (pickles opcache)
sudo systemctl reload php8.2-fpm
```

## 6. Cron / Scheduler

Add to crontab of the web user:

```cron
* * * * * cd /var/www/braga8 && php artisan schedule:run >> /dev/null 2>&1
```

This fires Laravel's scheduler every minute; the scheduler itself decides when
`reminders:send` runs (daily at 08:00).

## 7. Backups

| What | Strategy | Frequency |
| ------ | ---------- | ----------- |
| MySQL database | `mysqldump --single-transaction` → gzipped file → offsite. | Daily (nightly). |
| `storage/app/public/invoices` + `payments` | rsync to offsite bucket. | Daily. |
| `.env` | Stored in secrets manager (not in backup tarball). | On change. |
| Code | Git repository (GitHub/GitLab). | Continuous. |

Recommended: retain 7 daily + 4 weekly + 12 monthly snapshots.

## 8. TLS / HTTPS

- Terminate TLS at Nginx (Let's Encrypt via certbot, or org-managed cert).
- Redirect all HTTP → HTTPS.
- HSTS header: `Strict-Transport-Security: max-age=31536000; includeSubDomains`.
- MySQL connections: enforce TLS in `DB_HOST` config if DB is on a separate host.

## 9. Monitoring

| Signal | Tool | Action |
| -------- | ------ | -------- |
| Laravel errors | `storage/logs/laravel.log` + error tracking service (Sentry / Bugsnag). | Alert on `ERROR`+ severity. |
| Nginx 5xx | Nginx access/error logs + uptime monitor. | Alert on >1% 5xx rate. |
| DB slow queries | MySQL slow query log. | Review weekly. |
| Disk usage | `df` check via monitoring agent. | Alert at 80%. |
| Cron health | `schedule:run` heartbeat log. | Alert if no heartbeat in 2 min. |

## 10. Scaling Considerations

Current architecture is single-server. To scale:

1. **Move file storage to S3-compatible object store** — set `FILESYSTEM_DISK=s3`,

   update `config/filesystems.php`. Removes shared-disk dependency for multi-server.

2. **Move cache + session to Redis** — enables horizontal scaling of web tier.
3. **Add a queue worker** — set `QUEUE_CONNECTION=redis`, run `php artisan

   queue:work` as a systemd service. Move PDF generation and notification
   creation to queued jobs.

4. **Add a load balancer** in front of Nginx instances.
5. **Read replicas** for MySQL if read load grows (Laravel supports

   `read`/`write` connection splitting in `config/database.php`).
