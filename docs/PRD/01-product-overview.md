# 01 — Product Overview

## What It Is

**Braga8 Utility Billing** is a web-based operations platform that lets property management staff bill tenants for utility consumption (electricity and water) in multi-tenant residential or commercial buildings. It is an internal back-office tool with a small read-only API surface for tenant self-service.

## Problem Statement

Property managers that sub-meter electricity and water face manual, error-prone workflows:

- Meter readings are recorded on paper or spreadsheets and re-keyed into invoices.
- Tariffs change periodically and must be applied consistently across all units.
- Outstanding balances are hard to track; reminders are sent ad hoc.
- Complaints from tenants are lost in chat apps and email threads.
- There is no audit trail of who changed what, when.

Braga8 replaces this with a single Laravel application that owns the master data, billing logic, payment tracking, complaint workflow, and audit log.

## Target Users

| User Type | Role | Primary Need |
| ----------- | ------ | -------------- |
| Property manager / admin | `admin` | Full control: configure tariffs, users, units, generate reports |
| Field supervisor | `supervisor` | Oversee meter readings, review invoices, handle complaints |
| Field officer / meter reader | `petugas` | Enter meter readings, record payments, create complaints |
| Tenant | `tenant` | View own invoices, payment status, lodge complaints (via API) |

## Business Goals

1. **Accurate billing** — every billing period produces an invoice per occupied unit, calculated from actual meter deltas and the active tariff.
2. **Faster collection** — outstanding invoices are visible on the dashboard and can be reminded/escalated in one click.
3. **Accountability** — every create/update/delete on business records is written to `audit_logs` with actor, before/after, and timestamp.
4. **Tenant transparency** — tenants can see their own invoices and payment status without calling the office.
5. **Reporting** — monthly usage reports aggregate electricity/water consumption and expected revenue for management review.

## Scope

### In Scope

- Master data: tenants, units, utility meters, tariffs, users
- Operational data: meter readings, invoices, invoice items, payments
- Workflow: complaints, reminders, notifications
- Reporting: dashboard aggregates, monthly usage reports, audit log
- PDF invoice generation and per-invoice email/SMS-style notification

### Out of Scope

- Online payment gateway integration (payments are recorded manually by staff)
- Tenant self-registration (accounts are created by admin)
- Mobile native apps (tenant API can be consumed by a separate front-end)
- Utility provider integration / automated meter reading (AMR) ingestion
- Multi-property / multi-building support (single property assumed)

## Tech Stack Summary

- **Backend**: Laravel 12 on PHP 8.2
- **Auth**: Laravel Sanctum (token-based API) + session-based web (Laravel Breeze)
- **PDF**: barryvdh/laravel-dompdf
- **DB**: MySQL (default) — schema is portable to PostgreSQL with minor adjustments
- **Testing**: Pest
- **Static analysis**: Semgrep (custom rules in `.semgrep/braga8-custom.yml`)

## Glossary

| Term | Meaning |
| ------ | --------- |
| Unit | A rentable space (apartment / shop) inside the property |
| Utility Meter | A physical meter installed in a unit, measuring electricity (kWh) or water (m³) |
| Meter Reading | A periodic snapshot of a meter's cumulative value |
| Tariff | A pricing configuration: per-unit prices plus fixed fees and tax |
| Invoice | A bill for a billing period, with line items derived from readings + tariff |
| Invoice Item | One line on an invoice (electricity usage, water usage, fixed fees, tax) |
| Petugas | Indonesian for "officer" — the field-staff role |
