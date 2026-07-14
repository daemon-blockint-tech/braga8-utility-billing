# Product Requirements Documentation

This directory contains the Product Requirements Document (PRD) for the **Braga8 Utility Billing** application — a Laravel-based system for managing utility (electricity and water) billing in multi-tenant residential or commercial properties.

## Documents

| # | Document | Purpose |
| --- | ---------- | --------- |
| 01 | [Product Overview](01-product-overview.md) | What the product is, who it serves, business goals |
| 02 | [Roles & Permissions](02-roles-and-permissions.md) | User roles and their capabilities |
| 03 | [Functional Requirements](03-functional-requirements.md) | Feature breakdown per module |
| 04 | [Non-Functional Requirements](04-non-functional-requirements.md) | Performance, security, usability targets |
| 05 | [Business Rules](05-business-rules.md) | Billing logic, tariff calculation, escalation rules |

## Quick Summary

Braga8 Utility Billing is an internal operations platform used by property management staff to:

- Maintain a master list of **tenants**, **units**, and **utility meters**
- Record periodic **meter readings** for electricity and water
- Generate **invoices** from readings using configurable **tariffs**
- Track **payments** and outstanding balances
- Manage **complaints** reported by tenants or staff
- Schedule and dispatch **reminders** and **notifications**
- Produce **usage reports** and audit-trail dashboards

A read-only **tenant-facing API** exposes a limited subset (invoices, complaints, notifications, dashboard summary) to authenticated tenant accounts.
