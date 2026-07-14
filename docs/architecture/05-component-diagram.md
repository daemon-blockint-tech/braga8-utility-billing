# 05 — Component Diagram

## 1. High-Level Component View

```mermaid
flowchart LR
    subgraph Client[Client Tier]
        ADMIN[Admin Browser<br/>Blade + Tailwind]
        TENANT_WEB[Tenant Browser<br/>Blade + Tailwind]
        MOBILE[Flutter Mobile App<br/>Tenant only]
    end

    subgraph Server[Laravel Application]
        WEB[Web Router<br/>routes/web.php]
        API[API Router<br/>routes/api.php]
        MW[Middleware Stack<br/>auth, role, audit, csrf]
        CTRL[Controllers]
        MODEL[Eloquent Models]
        VALID[Form Requests]
        BLADE[Blade Views]
        PDF[DomPDF Engine]
        SCHED[Scheduler<br/>reminders:send]
    end

    subgraph Data[Data Tier]
        DB[(MySQL / MariaDB<br/>InnoDB)]
        FS[(File Storage<br/>storage/app)]
        CACHE[(Cache<br/>database driver)]
        SESSION[(Sessions Table)]
    end

    subgraph External[External]
        WA[WhatsApp<br/>wa.me deep link]
        MAIL[SMTP<br/>password reset]
    end

    ADMIN --> WEB
    TENANT_WEB --> WEB
    MOBILE --> API

    WEB --> MW
    API --> MW
    MW --> CTRL
    CTRL --> VALID
    VALID --> CTRL
    CTRL --> MODEL
    CTRL --> BLADE
    CTRL --> PDF
    BLADE --> ADMIN
    BLADE --> TENANT_WEB
    API --> MOBILE

    MODEL --> DB
    CTRL --> FS
    PDF --> FS
    SCHED --> CTRL
    DB --> SESSION
    DB --> CACHE

    CTRL -.builds link.-> WA
    CTRL -.sends.-> MAIL
```

## 2. Component Responsibilities

| Component | Responsibility | Technology |
| ----------- | --------------- | ------------ |
| Admin Browser | Full CRUD UI, dashboard, reports. | HTML + TailwindCSS + Alpine.js + Chart.js. |
| Tenant Browser | Limited self-service portal. | Same Blade stack, restricted views. |
| Flutter App | Mobile tenant experience: invoices, payments, complaints. | HTTP + bearer token to `/api/*`. |
| Web Router | Maps browser URLs to controllers. | Laravel routing. |
| API Router | Maps `/api/*` to API controllers. | Laravel + Sanctum. |
| Middleware | Auth, role check, audit log, CSRF. | Laravel middleware pipeline. |
| Controllers | Orchestrate request → model → view/response. | PHP 8.2. |
| Form Requests | Validate + authorize input. | Laravel FormRequest. |
| Eloquent Models | Data access + relationships. | Laravel Eloquent ORM. |
| Blade Views | Server-rendered HTML. | Laravel Blade + Tailwind. |
| DomPDF | Generate invoice PDFs. | barryvdh/laravel-dompdf. |
| Scheduler | Daily reminder job. | Laravel Scheduler + cron. |
| MySQL | Persistent storage. | InnoDB engine. |
| File Storage | Payment proofs + generated PDFs. | Local disk + `storage:link`. |

## 3. Request Flow — Web (Admin)

```mermaid
sequenceDiagram
    participant A as Admin Browser
    participant R as Web Router
    participant M as Middleware
    participant C as InvoiceController
    participant V as Form Request
    participant MD as Models
    participant DB as MySQL
    participant B as Blade View

    A->>R: GET /invoices/create
    R->>M: auth, role:admin
    M->>C: index()
    C->>MD: Unit::with('tenant','meters')->get()
    MD->>DB: SELECT ...
    DB-->>MD: rows
    MD-->>C: Collection
    C->>B: render invoices/create
    B-->>A: HTML form

    A->>R: POST /invoices (form data)
    R->>M: auth, role:admin, audit
    M->>V: StoreInvoiceRequest::rules()
    V-->>M: valid
    M->>C: store()
    C->>MD: DB::transaction { create invoice, items, notification }
    MD->>DB: INSERT ...
    C->>PDF: generate invoice PDF
    PDF->>DB: (no DB)
    PDF-->>C: PDF binary
    C->>FS: store under storage/app/public/invoices
    C->>B: redirect /invoices/{id}
    B-->>A: 302 → invoice detail
```

## 4. Request Flow — API (Mobile Payment)

```mermaid
sequenceDiagram
    participant F as Flutter App
    participant R as API Router
    participant M as Sanctum Middleware
    participant C as Api\PaymentController
    participant V as StorePaymentRequest
    participant MD as Models
    participant DB as MySQL

    F->>R: POST /api/payments (Bearer token, JSON + base64 image)
    R->>M: auth:sanctum, ability:braga8_auth_token
    M->>C: authenticated user
    C->>V: StorePaymentRequest::rules()
    V-->>C: valid
    C->>C: decode base64 image
    C->>FS: store decoded image to storage/app/public/payments
    C->>MD: Payment::create([status: pending, ...])
    MD->>DB: INSERT
    C-->>F: 201 { payment: {...} }
```

## 5. Build & Asset Pipeline

```mermaid
flowchart LR
    SRC[resources/css/app.css<br/>resources/js/app.js] --> VITE[Vite Dev Server / Build]
    TAIL[tailwind.config.js] --> VITE
    VITE --> PUB[public/build/<br/>hashed assets]
    PUB --> BLADE[Blade @vite directive]
```

- **Dev:** `npm run dev` starts Vite HMR server.
- **Prod:** `npm run build` compiles + hashes assets into `public/build/`.
- Blade's `@vite(['resources/css/app.css','resources/js/app.js'])` resolves to

  hashed filenames in production.
