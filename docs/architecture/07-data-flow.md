# 07 — Data Flow

This document traces the end-to-end data flow for the system's primary use cases.

## 1. Invoice Generation (Admin)

```mermaid
flowchart TD
    A[Admin selects unit + billing period] --> B[POST /invoices]
    B --> C[StoreInvoiceRequest validates]
    C --> D[InvoiceController::store]
    D --> E[DB::transaction]
    E --> F1[Create Invoice record status: unpaid]
    E --> F2[Create InvoiceItem rows per meter reading]
    E --> F3[Create Notification row type: invoice_created]
    F1 --> G[Generate PDF via DomPDF]
    G --> H[Store PDF in storage/app/public/invoices]
    H --> I[Commit transaction]
    I --> J[Redirect to invoice detail page]
```

**Inputs:** unit_id, billing period (month/year), per-meter previous + current
readings, tariff_id (auto-selected from unit's meters).

**Outputs:** `invoices` row, `invoice_items` rows, `notifications` row, PDF file on
disk, `audit_logs` row (via middleware).

**Side effects:** none beyond DB + filesystem. No outbound email/SMS at this stage.

## 2. Payment Upload (Tenant, Mobile)

```mermaid
flowchart TD
    A[Tenant selects invoice in app] --> B[POST /api/payments]
    B --> C[Sanctum auth + ability check]
    C --> D[StorePaymentRequest validates JSON]
    D --> E[PaymentController::store]
    E --> F[Decode base64 payment_proof]
    F --> G[Store image to storage/app/public/payments]
    G --> H[Create Payment row status: pending]
    H --> I[Create Notification type: payment_submitted]
    I --> J[Return 201 with payment resource]
```

**Inputs:** invoice_id, amount, payment_date, payment_method, payment_proof (base64
image string).

**Outputs:** `payments` row (status: pending), `notifications` row, image file on
disk.

**Admin follow-up:** admin opens web UI → Payments → verifies the uploaded proof →
clicks "Verify" → `PaymentController@verify` flips status to `verified` and marks
the linked invoice `paid`.

## 3. Daily Reminder Job

```mermaid
flowchart TD
    A[Cron fires schedule:run] --> B[Scheduler dispatches reminders:send at 08:00]
    B --> C[SendReminder command handle]
    C --> D[Query unpaid invoices where due_date <= today+3 days]
    D --> E{Any invoices?}
    E -- No --> F[Log 'No pending reminders']
    E -- Yes --> G[For each invoice]
    G --> H[Create Notification type: payment_reminder]
    H --> I[Log reminder created]
```

**Inputs:** none (reads DB state).

**Outputs:** `notifications` rows, log entries.

**Note:** the command does **not** send WhatsApp/SMS itself. It creates
notification records that admins can action via the web UI (which builds a
`wa.me` deep link).

## 4. Complaint Lifecycle

```mermaid
stateDiagram-v2
    [*] --> Open: Tenant creates complaint
    Open --> InProgress: Admin assigns / starts work
    InProgress --> Resolved: Admin marks resolved
    Resolved --> Closed: Tenant confirms or 7 days auto-close
    Closed --> [*]
    Resolved --> Reopened: Tenant disputes
    Reopened --> InProgress
```

**Data writes:**

- Tenant creates → `complaints` row (status: open) + `notifications` row.
- Admin updates status → `complaints.status` updated + `audit_logs` row.
- Tenant reopens → `complaints.status` reverted.

## 5. Audit Log Capture

```mermaid
flowchart LR
    A[Admin HTTP request] --> B[AuditLog middleware]
    B --> C{Method in POST/PUT/PATCH/DELETE?}
    C -- No --> D[Pass through]
    C -- Yes --> E[Capture user, action, module, ip, input]
    E --> F[Redact password fields]
    F --> G[Insert audit_logs row]
    G --> H[Pass to next middleware]
```

**Captured on:** every mutating admin request.
**Not captured on:** GET requests, API requests, tenant web requests.

## 6. Authentication (API)

```mermaid
sequenceDiagram
    participant App as Flutter App
    participant API as /api/login
    participant U as User Model
    participant S as Sanctum

    App->>API: POST {email, password, device_name}
    API->>U: User::where('email')->first()
    U-->>API: user or null
    alt no user
        API-->>App: 401 invalid credentials
    else user exists
        API->>U: Hash::check(password, user.password)
        alt password wrong
            API-->>App: 401 invalid credentials
        else password ok
            API->>U: user.role === 'admin'?
            alt admin
                API-->>App: 403 admin cannot use mobile
            else tenant
                API->>S: createToken('braga8_auth_token', ['braga8_auth_token'])
                S-->>API: plainTextToken
                API-->>App: 200 {token, user}
            end
        end
    end
```

## 7. PDF Generation

```mermaid
flowchart LR
    A[Controller calls PDF::loadView] --> B[DomPDF renders Blade template]
    B --> C[Template reads invoice + items + tenant + unit]
    C --> D[HTML → PDF conversion]
    D --> E[Save to storage/app/public/invoices/{id}.pdf]
    E --> F[Return path / stream to browser]
```

**Templates:** `resources/views/invoices/pdf.blade.php` (and any variant per
document type).

**Performance note:** PDF generation is synchronous. For high-volume billing
runs, this should be moved to a queued job (see Deployment Architecture §10).
