# Developer Guide

This guide is intended for developers who contribute to, maintain, or extend the
Braga8 Utility Billing application. It documents the local development workflow,
project structure, conventions, available tooling, and operational concerns.

## Audience

- Backend engineers working on the Laravel 12 codebase.
- Frontend engineers working on the Blade + Tailwind CSS + Alpine.js views.
- DevOps engineers packaging and deploying the application.
- New contributors onboarding to the project.

## How to Use This Guide

| If you want to... | Read this |
| --- | --- |
| Run the project locally for the first time | [01-getting-started.md](01-getting-started.md) |
| Understand the directory layout and conventions | [02-project-structure.md](02-project-structure.md) |
| Learn the artisan commands and scheduled jobs | [03-commands-and-scheduling.md](03-commands-and-scheduling.md) |
| Map the HTTP routes and controllers | [04-routes-and-controllers.md](04-routes-and-controllers.md) |
| Configure the application via `.env` and config files | [05-configuration.md](05-configuration.md) |
| Add or run tests | [06-testing.md](06-testing.md) |
| Reference the JSON API surface | [07-api-reference.md](07-api-reference.md) |
| Build assets and deploy the application | [08-deployment.md](08-deployment.md) |
| Follow coding standards and contribution rules | [09-coding-standards.md](09-coding-standards.md) |

## Related Documentation

- [Product Requirements (PRD)](../PRD/README.md)
- [Architecture](../architecture/README.md)
- [User Guide](../user/README.md)
- [Security Audit Report](../../SECURITY_AUDIT_REPORT.md)

## Tech Stack Summary

| Layer | Technology |
| --- | --- |
| Runtime | PHP 8.2+ |
| Framework | Laravel 12 |
| Database | SQLite (dev) / MySQL or PostgreSQL (prod) |
| Auth | Laravel Breeze + Sanctum |
| Frontend | Blade, Tailwind CSS 4, Alpine.js 3 |
| Asset bundler | Vite 6 |
| PDF generation | barryvdh/laravel-dompdf 3 |
| Testing | Pest 4 + pest-plugin-laravel |
| Code style | Laravel Pint |
| Local stack | Laravel Sail (Docker) optional |

See [08-technology-stack.md](../architecture/08-technology-stack.md) in the
architecture docs for the full dependency inventory.
