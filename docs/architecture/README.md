# Architecture Documentation

This directory documents the system architecture of the Braga8 Utility Billing application.

## Document Index

| # | Document | Description |
| --- | ---------- | ------------- |
| 01 | [System Overview](01-system-overview.md) | High-level architecture, component diagram, technology stack, and request flow. |
| 02 | [Data Architecture](02-data-architecture.md) | Database schema, entity relationships, migration history, and data flow. |
| 03 | [Application Architecture](03-application-architecture.md) | Layered structure: routes, controllers, models, middleware, business logic, and views. |
| 04 | [Security Architecture](04-security-architecture.md) | Authentication, role-based access control, audit logging, and API security. |
| 05 | [Component Diagram](05-component-diagram.md) | Module-level component relationships and dependencies. |
| 06 | [Deployment Architecture](06-deployment-architecture.md) | Build pipeline, runtime environment, scheduled jobs, and operational concerns. |
| 07 | [Data Flow](07-data-flow.md) | End-to-end data flow traces for primary use cases (invoice, payment, reminders, complaints, auth, PDF). |
| 08 | [Technology Stack](08-technology-stack.md) | Full dependency inventory, version pinning, and upgrade strategy. |

## Audience

- **Backend / full-stack engineers** onboarding to the codebase.
- **DevOps / SRE** planning deployment and observability.
- **Security reviewers** assessing the trust model and audit trail.
- **Product / QA** verifying that documented flows match the implementation.

## Conventions

- All diagrams use [Mermaid](https://mermaid.js.org/) so they render in GitHub, GitLab,

  and most Markdown viewers.

- File and class references use backticks (e.g. `app/Http/Controllers/InvoiceController.php`).
- The application UI is in Bahasa Indonesia; user-facing strings in this documentation are

  quoted verbatim and translated where helpful.
