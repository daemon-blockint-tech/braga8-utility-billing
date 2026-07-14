# 02 — Roles & Permissions

## Role Model

The application uses a single `role` string column on the `users` table. Four roles are recognised:

| Role | Description | Web Access | API Access |
| ------ | ------------- | ------------- | ------------ |
| `admin` | Property manager / system owner | Full | Full |
| `supervisor` | Field supervisor | Read + most writes | Read + most writes |
| `petugas` | Field officer / meter reader | Limited writes | Limited writes |
| `tenant` | End-user tenant | None (no web login) | Read-only, own data only |

## Capability Matrix

| Capability | admin | supervisor | petugas | tenant (API) |
| ------------ | :-----: | :----------: | :-------: | :------------: |
| Manage users (CRUD) | ✅ | ❌ | ❌ | ❌ |
| Manage tariffs (CRUD) | ✅ | read | ❌ | ❌ |
| Manage tenants (CRUD) | ✅ | ✅ | ❌ | ❌ |
| Manage units (CRUD) | ✅ | ✅ | ❌ | ❌ |
| Manage utility meters (CRUD) | ✅ | ✅ | ❌ | ❌ |
| Record meter readings | ✅ | ✅ | ✅ | ❌ |
| Generate / regenerate invoices | ✅ | ✅ | ❌ | ❌ |
| Record payments | ✅ | ✅ | ✅ | ❌ |
| Manage complaints (CRUD) | ✅ | ✅ | ✅ (create only) | create + read own |
| Send reminders | ✅ | ✅ | ❌ | ❌ |
| Send notifications | ✅ | ✅ | ❌ | ❌ |
| View dashboard | ✅ | ✅ | ✅ | own summary only |
| View audit logs | ✅ | read | ❌ | ❌ |
| View usage reports | ✅ | ✅ | ❌ | ❌ |
| View own invoices / payments | ✅ | ✅ | ❌ | ✅ (own only) |

## Enforcement Points

- **Web routes** (`routes/web.php`): protected by `auth` middleware. Role checks are performed inside each controller (e.g. `abort_unless(auth()->user()->role === 'admin', 403)`).
- **API routes** (`routes/api.php`): protected by `auth:sanctum`. Tenant-scoped endpoints additionally filter by `tenant_id = auth()->id()`.

> ⚠️ **Security note (from audit)**: The current implementation relies on in-controller role checks rather than a centralised middleware or policy layer. The security audit identified systemic BOLA (Broken Object Level Authorization) on resource routes — see `SECURITY_AUDIT_REPORT.md`. The capability matrix above describes the **intended** behaviour; the **actual** enforcement has gaps that are tracked as audit findings.
