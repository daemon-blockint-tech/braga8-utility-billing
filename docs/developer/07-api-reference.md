# 07 — API Reference

This document describes the public REST API exposed under `/api/v1/`.
The API is intended for programmatic access by authorized clients
(mobile apps, integrations, reporting tools).

## Authentication

The API uses **Laravel Sanctum** for token-based authentication.

### Obtaining a token

Tokens are issued via the web UI at `Profile → API Tokens`, or
programmatically by an authenticated user:

```http
POST /api/v1/tokens
Content-Type: application/json
Authorization: Bearer <existing-token>

{
  "name": "Mobile App",
  "abilities": ["customers:read", "invoices:read"]
}
```

Response `201 Created`:

```json
{
  "token": "1|abcdef123456...",
  "name": "Mobile App",
  "abilities": ["customers:read", "invoices:read"]
}
```

> The plain token value is only returned once. Store it securely.

### Using a token

Include the token in the `Authorization` header:

```http
Authorization: Bearer 1|abcdef123456...
```

### Revoking a token

```http
DELETE /api/v1/tokens/{tokenId}
Authorization: Bearer <token>
```

## Content Negotiation

- All requests and responses use JSON.
- Set `Content-Type: application/json` and `Accept: application/json`.
- Responses are UTF-8 encoded.
- Dates are ISO 8601 (`2026-04-01T12:00:00Z`).
- Monetary values are decimal strings (`"125.50"`) to avoid floating

  point precision loss.

## Pagination

List endpoints return paginated results. Default page size is 15.

```json
{
  "data": [ /* ... */ ],
  "links": {
    "first": "https://api.braga8.test/api/v1/customers?page=1",
    "last": "https://api.braga8.test/api/v1/customers?page=4",
    "prev": null,
    "next": "https://api.braga8.test/api/v1/customers?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 4,
    "per_page": 15,
    "to": 15,
    "total": 53
  }
}
```

Override page size with `?per_page=50` (max 100).

## Error Responses

All errors follow a consistent shape:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "name": ["The name may not be greater than 255 characters."]
  }
}
```

| Status | Meaning |
| --- | --- |
| `400` | Bad request — malformed JSON or invalid parameters |
| `401` | Unauthenticated — missing or invalid token |
| `403` | Forbidden — token lacks required abilities or user lacks role |
| `404` | Not found |
| `422` | Validation error |
| `429` | Rate limited — see `Retry-After` header |
| `500` | Server error |

## Rate Limiting

API routes are rate-limited via `throttle:api` (60 requests/minute per
token by default). The following response headers indicate current
usage:

| Header | Description |
| --- | --- |
| `X-RateLimit-Limit` | Maximum requests per window |
| `X-RateLimit-Remaining` | Remaining requests in current window |
| `Retry-After` | Seconds until reset (on 429 responses) |

## Endpoints

### Customers

#### List customers

```http
GET /api/v1/customers
```

**Abilities:** `customers:read`

**Query parameters:**

| Param | Type | Default | Description |
| --- | --- | --- | --- |
| `search` | string | — | Filter by name, email, or account number |
| `status` | string | `active` | `active`, `inactive`, `all` |
| `per_page` | int | 15 | Items per page (max 100) |
| `page` | int | 1 | Page number |
| `sort` | string | `name` | `name`, `-name`, `created_at`, `-created_at` |

**Response `200 OK`:**

```json
{
  "data": [
    {
      "id": 1,
      "account_number": "BRG-0001",
      "name": "Jane Doe",
      "email": "jane@example.com",
      "phone": "+628123456789",
      "address": "Jl. Merdeka 1, Bandung",
      "status": "active",
      "created_at": "2026-01-15T08:00:00Z",
      "updated_at": "2026-03-01T10:30:00Z"
    }
  ],
  "links": { /* ... */ },
  "meta": { /* ... */ }
}
```

#### Create a customer

```http
POST /api/v1/customers
```

**Abilities:** `customers:write`  
**Role:** `admin`

**Request body:**

| Field | Type | Required | Rules |
| --- | --- | --- | --- |
| `name` | string | yes | max 255 |
| `email` | string | yes | email, unique |
| `phone` | string | no | max 20 |
| `address` | string | no | max 500 |

**Response `201 Created`:** single customer object.

#### Retrieve a customer

```http
GET /api/v1/customers/{customer}
```

**Abilities:** `customers:read`

**Response `200 OK`:** single customer object with embedded meters and
recent invoices.

### Invoices

#### List invoices

```http
GET /api/v1/invoices
```

**Abilities:** `invoices:read`

**Query parameters:**

| Param | Type | Default | Description |
| --- | --- | --- | --- |
| `customer_id` | int | — | Filter by customer |
| `status` | string | — | `unpaid`, `paid`, `overdue`, `void` |
| `from` | date | — | Issue date range start (Y-m-d) |
| `to` | date | — | Issue date range end (Y-m-d) |
| `per_page` | int | 15 | Items per page |

**Response `200 OK`:**

```json
{
  "data": [
    {
      "id": 42,
      "number": "INV-2026-0042",
      "customer_id": 1,
      "issue_date": "2026-04-01",
      "due_date": "2026-04-15",
      "subtotal": "125.50",
      "tax": "12.55",
      "total": "138.05",
      "status": "unpaid",
      "items": [
        {
          "id": 101,
          "description": "Water consumption (120 m³)",
          "quantity": 120,
          "unit_price": "0.50",
          "total": "60.00"
        }
      ]
    }
  ]
}
```

#### Retrieve an invoice

```http
GET /api/v1/invoices/{invoice}
```

**Abilities:** `invoices:read`

**Response `200 OK`:** single invoice object with `items` and `payments`.

### Payments

#### Record a payment

```http
POST /api/v1/invoices/{invoice}/payments
```

**Abilities:** `payments:write`  
**Role:** `admin`

**Request body:**

| Field | Type | Required | Rules |
| --- | --- | --- | --- |
| `amount` | decimal | yes | min 0.01 |
| `method` | string | yes | `cash`, `transfer`, `qris` |
| `paid_at` | date | no | defaults to today |
| `reference` | string | no | max 100 |

**Response `201 Created`:**

```json
{
  "data": {
    "id": 7,
    "invoice_id": 42,
    "amount": "138.05",
    "method": "transfer",
    "paid_at": "2026-04-10",
    "reference": "TRX-987654",
    "created_at": "2026-04-10T14:22:00Z"
  }
}
```

If the payment satisfies the invoice balance, the invoice `status`
becomes `paid`.

## Webhooks

> Webhook delivery is a planned feature. The endpoint will be
> `POST /api/v1/webhooks/payments` and will be documented here once
> implemented.

## Versioning

The API is versioned via the URL prefix (`/api/v1/`). Breaking changes
require a new major version (`/api/v2/`) and a deprecation period for
the previous version.

Non-breaking additions (new fields, new endpoints) are made within the
existing version. Clients must ignore unknown fields.

## SDK Examples

### cURL

```bash
curl -X GET https://api.braga8.test/api/v1/customers \
  -H "Authorization: Bearer 1|abcdef123456" \
  -H "Accept: application/json"
```

### PHP (Guzzle)

```php
$response = $client->get('/api/v1/invoices', [
    'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Accept' => 'application/json',
    ],
    'query' => ['status' => 'unpaid'],
]);
```

### JavaScript (fetch)

```js
const res = await fetch('https://api.braga8.test/api/v1/invoices', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json',
  },
});
const { data } = await res.json();
```
