# 05 — Business Rules

This document captures the domain logic that governs billing, tariff calculation, and operational workflows. These rules are the source of truth for expected system behavior; controllers and models should implement them faithfully.

## 5.1 Tariff Structure

| Rule | Description |
| ------ | ------------- |
| BR-TAR-01 | A **tariff** defines the unit price for a utility type (electricity or water) for a specific tenant/property. |
| BR-TAR-02 | Tariffs have an optional `name` field (added via migration `2026_03_31_162328`) for human-readable identification. |
| BR-TAR-03 | A tariff may be **flat** (single rate per unit) or **tiered** (rate varies by consumption band). |
| BR-TAR-04 | Only one tariff should be active per (tenant, utility type) at any given time. |
| BR-TAR-05 | Tariff changes apply to invoices generated *after* the change; historical invoices retain the rate used at generation time. |

## 5.2 Meter Readings

| Rule | Description |
| ------ | ------------- |
| BR-MR-01 | A **meter reading** records the cumulative value on a meter at a point in time. |
| BR-MR-02 | Consumption for a billing period = `current_reading - previous_reading`. |
| BR-MR-03 | If `current_reading < previous_reading`, the meter may have been replaced or rolled over. Operator must manually reconcile before invoicing. |
| BR-MR-04 | A reading can only be recorded for an **active** meter attached to an **occupied** unit. |
| BR-MR-05 | Each reading captures: meter ID, reading value, reading date, recorded-by user. |

## 5.3 Invoice Generation

| Rule | Description |
| ------ | ------------- |
| BR-INV-01 | An **invoice** is generated from one or more meter readings within a billing period. |
| BR-INV-02 | Invoice line items are computed as `consumption × applicable tariff rate`. |
| BR-INV-03 | An invoice has a unique invoice number (system-generated, sequential per tenant). |
| BR-INV-04 | Invoice status transitions: `draft → issued → partially_paid → paid → void`. |
| BR-INV-05 | An invoice in `draft` can be edited; once `issued` it is immutable except for status changes driven by payments. |
| BR-INV-06 | A `void` invoice is retained for audit but excluded from outstanding balance calculations. |
| BR-INV-07 | Regenerating an invoice creates a new invoice record; the original is marked `void` with a reference to its replacement. |

## 5.4 Payments

| Rule | Description |
| ------ | ------------- |
| BR-PAY-01 | A **payment** is recorded against an invoice with amount, date, and method (cash, bank transfer, mobile money, etc.). |
| BR-PAY-02 | When the sum of payments ≥ invoice total, status auto-transitions to `paid`. |
| BR-PAY-03 | Partial payments transition the invoice to `partially_paid`. |
| BR-PAY-04 | Outstanding balance = `invoice_total − sum(payments)`. |
| BR-PAY-05 | A payment cannot exceed the outstanding balance (no negative balance). |
| BR-PAY-06 | Reversing a payment reverts the invoice status accordingly. |

## 5.5 Reminders & Notifications

| Rule | Description |
| ------ | ------------- |
| BR-REM-01 | The `SendReminder` console command runs on a schedule (e.g., daily) and dispatches reminders for invoices that are overdue or approaching due date. |
| BR-REM-02 | Reminder lead time and overdue threshold are configurable. |
| BR-REM-03 | Reminders are idempotent — running the command twice in one day does not send duplicate notifications for the same invoice. |
| BR-REM-04 | A reminder records: target invoice, recipient tenant, channel (email/SMS/in-app), sent-at timestamp. |

## 5.6 Complaints

| Rule | Description |
| ------ | ------------- |
| BR-CMP-01 | A **complaint** has a status lifecycle: `open → in_progress → resolved → closed`. |
| BR-CMP-02 | Complaints can be filed by tenants (via API) or staff (via web UI). |
| BR-CMP-03 | Only staff can transition a complaint to `resolved` or `closed`. |
| BR-CMP-04 | All status transitions are logged with actor and timestamp. |

## 5.7 Tenancy & Data Isolation

| Rule | Description |
| ------ | ------------- |
| BR-TEN-01 | Every billable entity (unit, meter, reading, invoice, payment, complaint) belongs to a **tenant** (property owner/management company). |
| BR-TEN-02 | A user authenticated as tenant A must never see, create, or modify records belonging to tenant B. |
| BR-TEN-03 | Admin/super-admin users may operate across tenants. |
| BR-TEN-04 | Deactivating a tenant cascades to read-only mode for all its data; no new invoices or payments can be created. |

> ⚠️ **Audit status**: BR-TEN-02 is **not enforced** on all API resource routes — see `SECURITY_AUDIT_REPORT.md` (BOLA findings). This is the highest-priority remediation item.

## 5.8 Audit Trail

| Rule | Description |
| ------ | ------------- |
| BR-AUD-01 | Every create, update, and delete on invoices, payments, tariffs, and complaints writes an audit log entry. |
| BR-AUD-02 | Audit entries are append-only — never edited or deleted. |
| BR-AUD-03 | Audit entries capture: entity type, entity ID, action, actor user ID, before/after payload, timestamp. |
