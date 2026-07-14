# 04 — Routes and Controllers

This document maps the application's HTTP routes to their controllers,
middleware, and authorization policies.

## Middleware Stack

Middleware is registered in `bootstrap/app.php`. The global stack runs
on every request; route middleware is applied selectively.

### Global middleware

| Middleware | Purpose |
| --- | --- |
| `Illuminate\Foundation\Http\Middleware\ValidatePostSize` | Rejects oversized POST bodies |
| `Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance` | Returns 503 during maintenance mode |
| `Illuminate\Cookie\Middleware\EncryptCookies` | Encrypts/decrypts cookies |
| `Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse` | Adds queued cookies |
| `Illuminate\Session\Middleware\StartSession` | Starts session |
| `Illuminate\View\Middleware\ShareErrorsFromSession` | Shares `$errors` with views |
| `Illuminate\Foundation\Http\Middleware\VerifyCsrfToken` | Validates CSRF tokens |
| `Illuminate\Routing\Middleware\SubstituteBindings` | Route model binding |

### Route middleware aliases

| Alias | Class | Purpose |
| --- | --- | --- |
| `auth` | `App\Http\Middleware\Authenticate` | Requires authenticated user |
| `guest` | `App\Http\Middleware\RedirectIfAuthenticated` | Redirects authed users away from guest pages |
| `verified` | `Illuminate\Auth\Middleware\EnsureEmailIsVerified` | Requires verified email |
| `admin` | `App\Http\Middleware\IsAdmin` | Requires `role = 'admin'` |
| `throttle` | `Illuminate\Routing\Middleware\ThrottleRequests` | Rate limiting |

## Web Routes (`routes/web.php`)

All web routes are prefixed with the standard Breeze authentication
routes and the application's domain routes.

### Authentication (Breeze)

| Method | URI | Name | Middleware | Controller |
| --- | --- | --- | --- | --- |
| GET | `/login` | `login` | `guest` | `AuthenticatedSessionController@create` |
| POST | `/login` | `login.store` | `guest`, `throttle:login` | `AuthenticatedSessionController@store` |
| POST | `/logout` | `logout` | `auth` | `AuthenticatedSessionController@destroy` |
| GET | `/register` | `register` | `guest` | `RegisteredUserController@create` |
| POST | `/register` | `register.store` | `guest` | `RegisteredUserController@store` |
| GET | `/forgot-password` | `password.request` | `guest` | `PasswordResetLinkController@create` |
| POST | `/forgot-password` | `password.email` | `guest` | `PasswordResetLinkController@store` |
| GET | `/reset-password/{token}` | `password.reset` | `guest` | `NewPasswordController@create` |
| POST | `/reset-password` | `password.store` | `guest` | `NewPasswordController@store` |
| GET | `/verify-email` | `verification.notice` | `auth`, `verified` | `EmailVerificationPromptController@__invoke` |
| GET | `/verify-email/{id}/{hash}` | `verification.verify` | `auth`, `throttle` | `VerifyEmailController@__invoke` |
| POST | `/email/verification-notification` | `verification.send` | `auth`, `throttle` | `EmailVerificationNotificationController@store` |
| GET | `/confirm-password` | `password.confirm` | `auth` | `ConfirmablePasswordController@show` |
| POST | `/confirm-password` | `password.confirm.store` | `auth` | `ConfirmablePasswordController@store` |
| PUT | `/password` | `password.update` | `auth` | `PasswordController@update` |

### Profile

| Method | URI | Name | Middleware | Controller |
| --- | --- | --- | --- | --- |
| GET | `/profile` | `profile.edit` | `auth` | `ProfileController@edit` |
| PATCH | `/profile` | `profile.update` | `auth` | `ProfileController@update` |
| DELETE | `/profile` | `profile.destroy` | `auth` | `ProfileController@destroy` |

### Dashboard

| Method | URI | Name | Middleware | Controller |
| --- | --- | --- | --- | --- |
| GET | `/dashboard` | `dashboard` | `auth`, `verified` | `DashboardController@index` |

### Customers

| Method | URI | Name | Middleware | Controller |
| --- | --- | --- | --- | --- |
| GET | `/customers` | `customers.index` | `auth`, `verified` | `CustomerController@index` |
| GET | `/customers/create` | `customers.create` | `auth`, `verified`, `admin` | `CustomerController@create` |
| POST | `/customers` | `customers.store` | `auth`, `verified`, `admin` | `CustomerController@store` |
| GET | `/customers/{customer}` | `customers.show` | `auth`, `verified` | `CustomerController@show` |
| GET | `/customers/{customer}/edit` | `customers.edit` | `auth`, `verified`, `admin` | `CustomerController@edit` |
| PUT/PATCH | `/customers/{customer}` | `customers.update` | `auth`, `verified`, `admin` | `CustomerController@update` |
| DELETE | `/customers/{customer}` | `customers.destroy` | `auth`, `verified`, `admin` | `CustomerController@destroy` |

### Meters

| Method | URI | Name | Middleware | Controller |
| --- | --- | --- | --- | --- |
| GET | `/customers/{customer}/meters` | `meters.index` | `auth`, `verified` | `MeterController@index` |
| POST | `/customers/{customer}/meters` | `meters.store` | `auth`, `verified`, `admin` | `MeterController@store` |
| GET | `/meters/{meter}/readings` | `readings.index` | `auth`, `verified` | `MeterReadingController@index` |
| POST | `/meters/{meter}/readings` | `readings.store` | `auth`, `verified`, `admin` | `MeterReadingController@store` |

### Tariffs

| Method | URI | Name | Middleware | Controller |
| --- | --- | --- | --- | --- |
| GET | `/tariffs` | `tariffs.index` | `auth`, `verified`, `admin` | `TariffController@index` |
| POST | `/tariffs` | `tariffs.store` | `auth`, `verified`, `admin` | `TariffController@store` |
| PUT/PATCH | `/tariffs/{tariff}` | `tariffs.update` | `auth`, `verified`, `admin` | `TariffController@update` |
| DELETE | `/tariffs/{tariff}` | `tariffs.destroy` | `auth`, `verified`, `admin` | `TariffController@destroy` |

### Invoices

| Method | URI | Name | Middleware | Controller |
| --- | --- | --- | --- | --- |
| GET | `/invoices` | `invoices.index` | `auth`, `verified` | `InvoiceController@index` |
| GET | `/invoices/{invoice}` | `invoices.show` | `auth`, `verified` | `InvoiceController@show` |
| GET | `/invoices/{invoice}/pdf` | `invoices.pdf` | `auth`, `verified` | `InvoiceController@pdf` |
| POST | `/invoices/{invoice}/payments` | `payments.store` | `auth`, `verified`, `admin` | `PaymentController@store` |

### Reports

| Method | URI | Name | Middleware | Controller |
| --- | --- | --- | --- | --- |
| GET | `/reports` | `reports.index` | `auth`, `verified`, `admin` | `ReportController@index` |
| GET | `/reports/revenue` | `reports.revenue` | `auth`, `verified`, `admin` | `ReportController@revenue` |
| GET | `/reports/outstanding` | `reports.outstanding` | `auth`, `verified`, `admin` | `ReportController@outstanding` |

## API Routes (`routes/api.php`)

API routes are prefixed with `/api` and protected by Sanctum. They are
intended for programmatic access (mobile clients, integrations).

| Method | URI | Name | Middleware | Controller |
| --- | --- | --- | --- | --- |
| GET | `/api/v1/customers` | `api.customers.index` | `auth:sanctum` | `Api\CustomerController@index` |
| POST | `/api/v1/customers` | `api.customers.store` | `auth:sanctum`, `admin` | `Api\CustomerController@store` |
| GET | `/api/v1/customers/{customer}` | `api.customers.show` | `auth:sanctum` | `Api\CustomerController@show` |
| GET | `/api/v1/invoices` | `api.invoices.index` | `auth:sanctum` | `Api\InvoiceController@index` |
| GET | `/api/v1/invoices/{invoice}` | `api.invoices.show` | `auth:sanctum` | `Api\InvoiceController@show` |
| POST | `/api/v1/invoices/{invoice}/payments` | `api.payments.store` | `auth:sanctum`, `admin` | `Api\PaymentController@store` |

> API responses use Eloquent API Resources defined in
> `app/Http/Resources/`. See [07-api-reference.md](07-api-reference.md)
> for request/response schemas.

## Listing Routes

To inspect the full route table at any time:

```bash
php artisan route:list
php artisan route:list --path=customers
php artisan route:list --name=invoices
```

## Controller Conventions

- Controllers use **resource methods** (`index`, `create`, `store`,

  `show`, `edit`, `update`, `destroy`) where the domain maps cleanly to
  RESTful resources.

- **Form Requests** handle validation. Controllers receive pre-validated

  data and stay focused on orchestration.

- **Policies** handle authorization. Controllers call

  `authorize('action', $model)` or rely on route middleware.

- **API Resources** transform models into JSON responses, keeping

  serialization concerns out of controllers and models.

- Flash messages use `session()->flash()` for user-facing confirmations.
- Redirects use named routes: `redirect()->route('customers.show', $c)`.

## Adding a New Route

1. Add the route to `routes/web.php` (or `routes/api.php` for API).
2. Create the controller method (or generate the whole controller with

   `php artisan make:controller DomainController --resource`).

3. Create a form request for any non-trivial input:

   `php artisan make:request StoreDomainRequest`.

4. Create a policy if authorization is required:

   `php artisan make:policy DomainPolicy --model=Domain`.

5. Register the policy in `app/Providers/AuthServiceProvider` (or rely

   on auto-discovery).

6. Create the Blade view(s) under `resources/views/<domain>/`.
7. Write feature tests under `tests/Feature/<Domain>/`.
8. Update this document.
