# 03 — Functional Requirements

Functional requirements are grouped by module. Each requirement is numbered `FR-<module>-<n>`.

## 3.1 User Management (FR-USR)

| ID | Requirement | Priority |
| ---- | ------------- | ---------- |
| FR-USR-01 | Admin can create, view, update, and deactivate user accounts | Must |
| FR-USR-02 | Each user has a role (`admin`, `supervisor`, `petugas`, `tenant`) | Must |
| FR-USR-03 | Admin can reset a user's password | Must |
| FR-USR-04 | A user can update their own profile (name, email, password) | Should |
| FR-USR-05 | Tenant accounts are linked 1:1 to a `tenants` record | Must |
| FR-USR-06 | Deactivating a user revokes their tokens and prevents login | Should |

## 3.2 Tenant Management (FR-TNT)

| ID | Requirement | Priority |
| ---- | ------------- | ---------- |
| FR-TNT-01 | Admin/supervisor can CRUD tenants | Must |
| FR-TNT-02 | Each tenant has a name, contact info, and is assigned to one unit | Must |
| FR-TNT-03 | A tenant can be marked active/inactive | Must |
| FR-TNT-04 | Tenant contact info is validated (email format, phone length) | Should |

## 3.3 Unit Management (FR-UNT)

| ID | Requirement | Priority |
| ---- | ------------- | ---------- |
| FR-UNT-01 | Admin/supervisor can CRUD units | Must |
| FR-UNT-02 | Each unit has a code (e.g. `A-101`), type, and floor | Must |
| FR-UNT-03 | A unit can have one active tenant at a time | Must |
| FR-UNT-04 | Unit status: `occupied` / `vacant` | Must |

## 3.4 Utility Meter Management (FR-MTR)

| ID | Requirement | Priority |
| ---- | ------------- | ---------- |
| FR-MTR-01 | Admin/supervisor can CRUD utility meters | Must |
| FR-MTR-02 | Each meter is assigned to a unit and has a type (`electricity` / `water`) | Must |
| FR-MTR-03 | Each meter has a serial number and an initial reading | Must |
| FR-MTR-04 | A unit can have at most one active meter per type | Must |
| FR-MTR-05 | Meter status: `active` / `replaced` / `retired` | Should |

## 3.5 Tariff Management (FR-TAR)

| ID | Requirement | Priority |
| ---- | ------------- | ---------- |
| FR-TAR-01 | Admin can CRUD tariffs | Must |
| FR-TAR-02 | A tariff defines: electricity per-kWh price, water per-m³ price, fixed fees, tax rate | Must |
| FR-TAR-03 | A tariff has effective `start_date` and optional `end_date` | Must |
| FR-TAR-04 | Only one tariff is active for a given billing date | Must |
| FR-TAR-05 | Historical invoices retain the tariff values used at generation time | Must |

## 3.6 Meter Reading (FR-RDG)

| ID | Requirement | Priority |
| ---- | ------------- | ---------- |
| FR-RDG-01 | Admin/supervisor/petugas can record a meter reading | Must |
| FR-RDG-02 | A reading references a meter, has a value, a reading date, and a reader | Must |
| FR-RDG-03 | Reading value must be ≥ the previous reading on the same meter | Must |
| FR-RDG-04 | Reading date cannot be in the future | Must |
| FR-RDG-05 | Duplicate readings (same meter + same date) are rejected | Should |

## 3.7 Invoice Generation (FR-INV)

| ID | Requirement | Priority |
| ---- | ------------- | ---------- |
| FR-INV-01 | Admin/supervisor can generate invoices for a billing period | Must |
| FR-INV-02 | Invoice generation uses the latest readings per meter for the period | Must |
| FR-INV-03 | Usage = current reading − previous reading (or initial reading if first period) | Must |
| FR-INV-04 | Line items: electricity usage, water usage, fixed fees, tax | Must |
| FR-INV-05 | Each line item stores the unit price and quantity used at generation time | Must |
| FR-INV-06 | Invoice status: `draft` → `issued` → `paid` / `partial` / `overdue` | Must |
| FR-INV-07 | Admin can regenerate an invoice in `draft` status | Should |
| FR-INV-08 | A PDF invoice can be generated and downloaded | Must |

## 3.8 Payment Tracking (FR-PAY)

| ID | Requirement | Priority |
| ---- | ------------- | ---------- |
| FR-PAY-01 | Admin/supervisor/petugas can record a payment against an invoice | Must |
| FR-PAY-02 | A payment has amount, method (`cash` / `transfer` / `qr`), date, and reference | Must |
| FR-PAY-03 | Invoice status auto-updates to `paid` when total payments ≥ invoice total | Must |
| FR-PAY-04 | Partial payments are supported; status becomes `partial` | Should |
| FR-PAY-05 | Payments cannot exceed the invoice total | Must |

## 3.9 Complaints (FR-CMP)

| ID | Requirement | Priority |
| ---- | ------------- | ---------- |
| FR-CMP-01 | Staff can create a complaint on behalf of a tenant | Must |
| FR-CMP-02 | Tenant can create a complaint via API | Should |
| FR-CMP-03 | A complaint has category, description, priority, and status | Must |
| FR-CMP-04 | Complaint status: `open` → `in_progress` → `resolved` / `closed` | Must |
| FR-CMP-05 | Admin/supervisor can update status and add resolution notes | Must |
| FR-CMP-06 | Tenant can view only their own complaints via API | Must |

## 3.10 Reminders & Notifications (FR-RMD)

| ID | Requirement | Priority |
| ---- | ------------- | ---------- |
| FR-RMD-01 | Admin/supervisor can send a reminder for an overdue/partial invoice | Must |
| FR-RMD-02 | A scheduled command (`SendReminder`) scans for overdue invoices and queues reminders | Must |
| FR-RMD-03 | Admin/supervisor can send ad-hoc notifications to tenants | Should |
| FR-RMD-04 | Each reminder/notification is logged with recipient, channel, and timestamp | Must |

## 3.11 Reporting (FR-RPT)

| ID | Requirement | Priority |
| ---- | ------------- | ---------- |
| FR-RPT-01 | Dashboard shows KPIs: total receivables, overdue count, collection rate, active complaints | Must |
| FR-RPT-02 | Monthly usage report: per-unit electricity/water consumption and billed amount | Must |
| FR-RPT-03 | Reports can be filtered by date range and unit | Should |
| FR-RPT-04 | Audit log viewable by admin (all actors) and supervisor (read-only) | Must |

## 3.12 Audit Logging (FR-AUD)

| ID | Requirement | Priority |
| ---- | ------------- | ---------- |
| FR-AUD-01 | Every create/update/delete on business records writes to `audit_logs` | Must |
| FR-AUD-02 | Log entry stores: actor user id, action, entity type, entity id, before/after JSON, timestamp | Must |
| FR-AUD-03 | Audit log is append-only (no update/delete) | Must |
| FR-AUD-04 | Admin can filter audit log by actor, entity, date range | Should |
