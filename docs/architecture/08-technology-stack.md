# 08 — Technology Stack

## 1. Stack Summary

| Layer | Technology | Version | Notes |
| ------- | ----------- | --------- | ------- |
| Language | PHP | 8.2+ | Typed properties, readonly classes, enums. |
| Framework | Laravel | 12.x | LTS-style framework; standard request lifecycle. |
| Auth (web) | Laravel Breeze | latest | Login, register, password reset, profile. |
| Auth (API) | Laravel Sanctum | latest | Token-based, ability-scoped. |
| Database | MySQL / MariaDB | 8.0 / 10.6+ | InnoDB engine, UTF-8mb4. |
| ORM | Eloquent | (bundled) | ActiveRecord-style models. |
| Frontend markup | Blade | (bundled) | Server-rendered. |
| CSS framework | TailwindCSS | 3.x | Utility-first; configured via `tailwind.config.js`. |
| JS bundler | Vite | 5.x | Dev HMR + production build. |
| Client JS | Alpine.js | 3.x | Lightweight reactivity for dropdowns/modals. |
| Charts | Chart.js | 4.x | Dashboard visualizations. |
| PDF | barryvdh/laravel-dompdf | latest | HTML → PDF via DomPDF wrapper. |
| HTTP client (mobile) | axios | latest | Used by Flutter via JS interop / direct HTTP. |
| Testing | Pest PHP | 2.x | PHPUnit-compatible, expressive DSL. |
| Test server | Laravel `php artisan serve` | — | Local dev only. |

## 2. Why These Choices

### Laravel 12

- Mature, well-documented framework with strong conventions (routing, Eloquent,

  middleware, Blade). Reduces bespoke plumbing.

- Built-in scheduler, queue system, and mailer cover operational needs without

  extra libraries.

### Sanctum (not Passport)

- Mobile app needs simple per-device tokens, not OAuth2 flows.
- Sanctum's ability-scoped tokens (`createToken('name', ['ability'])`) match the

  single-ability `braga8_auth_token` pattern used here.

### Blade + Tailwind (not SPA)

- Admin dashboard is internal, server-rendered pages are sufficient.
- Avoids the complexity of a separate SPA build + API versioning for the web UI.
- Tailwind utility classes keep styling consistent without a heavy design system.

### Flutter (mobile)

- Cross-platform from a single Dart codebase.
- Talks to the same Laravel API via bearer tokens.

### DomPDF

- Pure-PHP PDF generation; no external binary dependency (unlike wkhtmltopdf).
- Adequate for invoice layouts. For complex layouts, consider upgrading to

  `laravel-snappy` or a headless Chromium service.

### Pest PHP

- Cleaner test DSL than raw PHPUnit; fully PHPUnit-compatible so existing

  Laravel testing helpers work unchanged.

## 3. Dependency Inventory

### Composer (`composer.json`)

| Package | Purpose |
| --------- | --------- |
| `laravel/framework` | Core framework. |
| `laravel/sanctum` | API token auth. |
| `laravel/breeze` | Auth scaffolding (installed once; code lives in repo). |
| `barryvdh/laravel-dompdf` | PDF generation. |
| `guzzlehttp/guzzle` | HTTP client (transitive; used by framework mail/queue). |

### NPM (`package.json`)

| Package | Purpose |
| --------- | --------- |
| `laravel-vite-plugin` | Vite integration for Blade. |
| `vite` | Asset bundler. |
| `tailwindcss` | Utility CSS. |
| `autoprefixer`, `postcss` | Tailwind toolchain. |
| `alpinejs` | Client-side reactivity. |
| `axios` | HTTP client (used by mobile interop + admin AJAX). |
| `chart.js` | Dashboard charts. |

## 4. Versioning & Upgrade Strategy

- **PHP:** pin to 8.2 minimum; test on 8.3 before adopting.
- **Laravel:** follow LTS releases; run `php artisan about` after each upgrade

  to verify config + env consistency.

- **Frontend deps:** `npm outdated` review quarterly; bump minor versions in

  batches, major versions individually with regression testing.

- **DomPDF:** track upstream; DomPDF has historically had CVEs — subscribe to

  security advisories.

## 5. Tooling

| Tool | Use |
| ------ | ----- |
| Composer | PHP dependency management. |
| npm | JS dependency management. |
| Artisan | Migrations, scheduler, seeders, custom commands. |
| Pest | Test runner (`php artisan test` or `vendor/bin/pest`). |
| Vite | Asset build. |
| `php artisan serve` | Local dev server. |
| `php artisan tinker` | REPL for model inspection. |
| `php artisan route:list` | Route inspection. |
| `php artisan migrate:fresh --seed` | Reset local DB. |
