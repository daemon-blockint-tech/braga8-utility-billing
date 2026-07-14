# 04 — Non-Functional Requirements

## 4.1 Performance

| ID | Requirement | Target |
| ---- | ------------- | -------- |
| NFR-PER-01 | Dashboard loads in under 2s with 10k invoices | p95 < 2s |
| NFR-PER-02 | Invoice generation for 500 units completes in under 30s | < 30s |
| NFR-PER-03 | API list endpoints respond in under 500ms for 1k records | p95 < 500ms |
| NFR-PER-04 | PDF generation for a single invoice completes in under 3s | < 3s |

## 4.2 Security

| ID | Requirement |
| ---- | ------------- |
| NFR-SEC-01 | All routes require authentication (no anonymous access) |
| NFR-SEC-02 | Role-based authorization enforced on every state-changing action |
| NFR-SEC-03 | Tenant-scoped data isolation: a tenant can only access their own records |
| NFR-SEC-04 | Passwords hashed with bcrypt (Laravel default) |
| NFR-SEC-05 | API tokens issued via Sanctum, revocable, scoped to user |
| NFR-SEC-06 | CSRF protection on web forms (Laravel default) |
| NFR-SEC-07 | SQL injection protection via Eloquent parameter binding |
| NFR-SEC-08 | XSS protection via Blade `{{ }}` escaping on all output |
| NFR-SEC-09 | Audit log captures all create/update/delete with actor identity |
| NFR-SEC-10 | No secrets in source control; `.env` is git-ignored |

> ⚠️ **Audit status**: The security audit (`SECURITY_AUDIT_REPORT.md`) identified 22 validated findings. NFR-SEC-02 and NFR-SEC-03 are **not fully met** in the current implementation — BOLA vulnerabilities exist on resource routes. Remediation is tracked separately.

## 4.3 Reliability & Availability

| ID | Requirement |
| ---- | ------------- |
| NFR-REL-01 | Application is designed for single-instance deployment (no HA requirement) |
| NFR-REL-02 | Database backups are the operator's responsibility (Laravel does not manage these) |
| NFR-REL-03 | Scheduled commands (`SendReminder`) are idempotent — safe to retry |
| NFR-REL-04 | Failed invoice generation for one unit does not block others in the same batch |

## 4.4 Maintainability

| ID | Requirement |
| ---- | ------------- |
| NFR-MAINT-01 | Code follows PSR-12 + Laravel conventions |
| NFR-MAINT-02 | Pest test suite covers auth, registration, and core billing flows |
| NFR-MAINT-03 | Semgrep custom rules in `.semgrep/braga8-custom.yml` enforce project-specific patterns |
| NFR-MAINT-04 | All business logic lives in controllers + models (no service layer abstraction yet) |
| NFR-MAINT-05 | Database migrations are versioned and reversible |

## 4.5 Usability

| ID | Requirement |
| ---- | ------------- |
| NFR-UX-01 | Web UI is responsive and works on tablets (field staff use tablets) |
| NFR-UX-02 | All forms have server-side validation with user-friendly error messages |
| NFR-UX-03 | Critical actions (delete, regenerate invoice) require confirmation |
| NFR-UX-04 | Dashboard is the landing page after login |

## 4.6 Compatibility

| ID | Requirement |
| ---- | ------------- |
| NFR-COMP-01 | PHP 8.2+ |
| NFR-COMP-02 | Laravel 12.x |
| NFR-COMP-03 | MySQL 8.0+ (or MariaDB 10.6+) |
| NFR-COMP-04 | Modern browsers (Chrome, Firefox, Safari, Edge — last 2 versions) |

## 4.7 Data Retention

| ID | Requirement |
| ---- | ------------- |
| NFR-RET-01 | Invoices and payments are retained indefinitely (legal/financial records) |
| NFR-RET-02 | Audit logs are retained indefinitely |
| NFR-RET-03 | Meter readings are retained indefinitely (basis for historical usage reports) |
| NFR-RET-04 | Deactivated users are soft-deleted (preserved for referential integrity) |
