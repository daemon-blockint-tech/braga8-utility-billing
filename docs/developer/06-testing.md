# 06 — Testing

The application uses **Pest PHP** (built on PHPUnit) for testing. Tests
live in the `tests/` directory and are organized into **Unit** and
**Feature** suites.

## Running Tests

```bash
# Run the full suite
php artisan test

# Run with parallel execution
php artisan test --parallel

# Run only unit tests
php artisan test --testsuite=Unit

# Run only feature tests
php artisan test --testsuite=Feature

# Run a specific file
php artisan test tests/Feature/Auth/RegistrationTest.php

# Run a specific test by name
php artisan test --filter='it_can_create_a_customer'

# Run with coverage
php artisan test --coverage

# Run with coverage and minimum threshold
php artisan test --coverage --min=80
```

## Test Database

Tests use an in-memory SQLite database defined in `phpunit.xml`:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

This gives fast, isolated test runs. The `RefreshDatabase` trait migrates
the schema fresh for each test class.

If your migrations use MySQL-specific features that SQLite does not
support, either:

1. Guard the offending code with `DB::getDriverName()` checks, or
2. Use a dedicated test MySQL database by overriding `DB_DATABASE` in

   `phpunit.xml` or a local `phpunit.xml.local` override.

## Test Structure

```text
tests/
├── Feature/                  # HTTP-level tests
│   ├── Auth/                 # Authentication flows
│   │   ├── RegistrationTest.php
│   │   ├── LoginTest.php
│   │   └── PasswordResetTest.php
│   ├── Customer/
│   │   ├── CreateCustomerTest.php
│   │   └── ViewCustomerTest.php
│   ├── Invoice/
│   │   └── InvoiceTest.php
│   └── Console/
│       └── SendRemindersTest.php
├── Unit/                     # Pure logic tests
│   ├── ExampleTest.php
│   └── Billing/
│       └── InvoiceCalculatorTest.php
├── Pest.php                  # Pest bootstrap
└── TestCase.php              # Base test class
```

## Pest Configuration (`tests/Pest.php`)

The Pest bootstrap file imports traits and defines helper functions:

```php
uses(
    Illuminate\Foundation\Testing\RefreshDatabase::class,
    Illuminate\Foundation\Testing\WithFaker::class,
)->in('Feature', 'Unit');

// Helper to create an admin user
function adminUser(): User
{
    return User::factory()->admin()->create();
}
```

## Writing a Feature Test

Feature tests exercise the full HTTP stack: routing, middleware,
controllers, database, and views.

```php
<?php

use App\Models\Customer;
use App\Models\User;
use function Pest\Laravel\{actingAs, assertDatabaseHas, get, post};

it('displays the customer list', function () {
    $user = User::factory()->create();
    Customer::factory()->count(3)->create();

    $response = actingAs($user)->get(route('customers.index'));

    $response->assertOk();
    $response->assertSee(Customer::first()->name);
});

it('prevents non-admins from creating customers', function () {
    $user = User::factory()->create(['role' => 'staff']);

    $response = actingAs($user)->post(route('customers.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);

    $response->assertForbidden();
});
```

## Writing a Unit Test

Unit tests focus on isolated logic — service classes, helpers, value
objects — without booting the HTTP kernel.

```php
<?php

use App\Billing\InvoiceCalculator;

it('calculates tiered charges correctly', function () {
    $calculator = new InvoiceCalculator();

    $result = $calculator->calculate(150, [
        ['up_to' => 100, 'rate' => 0.50],
        ['up_to' => null, 'rate' => 0.75],
    ]);

    expect($result)->toBe((100 * 0.50) + (50 * 0.75));
});
```

## Factories

Model factories are defined in `database/factories/`. They use the
`Factory` class and provide states for common scenarios.

```php
// database/factories/UserFactory.php
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => 'staff',
        ];
    }

    public function admin(): static
    {
        return $this->state(['role' => 'admin']);
    }
}
```

Usage in tests:

```php
$user = User::factory()->admin()->create();
$customers = Customer::factory()->count(5)->create();
```

## Seeders

Seeders in `database/seeders/` populate the database with deterministic
data for local development and staging.

```bash
php artisan db:seed
php artisan db:seed --class=CustomerSeeder
php artisan migrate:fresh --seed   # reset + seed
```

## Testing Commands

Console commands can be tested by calling `Artisan::call()` or using
Pest's `artisan()` helper:

```php
it('marks overdue invoices', function () {
    Invoice::factory()->create([
        'status' => 'unpaid',
        'due_date' => now()->subDay(),
    ]);

    Artisan::call('billing:generate-overdue');

    expect(Invoice::first()->status)->toBe('overdue');
});
```

## Testing Notifications

Use `Notification::fake()` to assert notifications are dispatched without
actually sending them:

```php
use Illuminate\Support\Facades\Notification;

it('sends a reminder notification', function () {
    Notification::fake();

    // ... trigger reminder logic ...

    Notification::assertSentTo($customer, SendReminderNotification::class);
});
```

## Testing Jobs

Use `Queue::fake()` for queue assertions:

```php
use Illuminate\Support\Facades\Queue;

it('dispatches a payment processing job', function () {
    Queue::fake();

    // ... trigger payment ...

    Queue::assertPushed(ProcessPaymentJob::class);
});
```

## Testing File Uploads

Use `Illuminate\Http\UploadedFile::fake()`:

```php
it('accepts a meter reading photo upload', function () {
    Storage::fake('local');

    $file = UploadedFile::fake()->image('reading.jpg');

    $response = actingAs($user)->post(route('readings.store'), [
        'meter_id' => $meter->id,
        'value' => 1234,
        'photo' => $file,
    ]);

    Storage::disk('local')->assertExists('readings/'.$file->hashName());
});
```

## Continuous Integration

Tests run automatically on every push via GitHub Actions (see
`.github/workflows/tests.yml`). The CI pipeline:

1. Checks out the code.
2. Sets up PHP 8.2+ with required extensions.
3. Installs Composer dependencies (cached).
4. Copies `.env.testing` to `.env`.
5. Runs `php artisan key:generate`.
6. Runs `php artisan test --parallel`.

Failing tests block the pull request from merging.

## Best Practices

- **One assertion per test** where practical; combine only when setup

  is expensive.

- **Use factories, not manual model creation.** They keep tests readable

  and maintainable.

- **Name tests descriptively** — Pest's `it('does X')` style reads as a

  specification.

- **Test behavior, not implementation.** Assert on outputs and database

  state, not on private method calls.

- **Keep tests fast.** In-memory SQLite, `RefreshDatabase`, and minimal

  seeding keep the suite under a few seconds.

- **Avoid hitting external services.** Fake mail, queue, HTTP, and

  storage in tests.
