# 03 — Commands and Scheduling

This document lists every Artisan command shipped with Braga8 Utility
Billing, explains the scheduled jobs, and shows how to run and test them.

## Built-in Laravel Commands

The application inherits the full Laravel 12 Artisan command set. The
most commonly used commands during development are:

| Command | Purpose |
| --- | --- |
| `php artisan serve` | Start the local development server |
| `php artisan migrate` | Run pending migrations |
| `php artisan migrate:rollback` | Roll back the last migration batch |
| `php artisan migrate:fresh --seed` | Drop all tables and re-seed |
| `php artisan tinker` | Interactive REPL on the application |
| `php artisan route:list` | List all registered routes |
| `php artisan queue:work` | Start a queue worker |
| `php artisan queue:failed` | List failed jobs |
| `php artisan queue:retry all` | Retry all failed jobs |
| `php artisan schedule:run` | Run scheduled commands due now |
| `php artisan schedule:work` | Run scheduler in foreground (dev) |
| `php artisan test` | Run the Pest test suite |
| `php artisan db:seed` | Run database seeders |
| `php artisan storage:link` | Symlink `storage/app/public` to `public/storage` |
| `php artisan optimize` | Cache config, routes, events, and views |
| `php artisan optimize:clear` | Clear all framework caches |
| `php artisan key:generate` | Generate `APP_KEY` |
| `php artisan vendor:publish --tag=...` | Publish vendor assets/config |

## Application Commands

### `billing:send-reminders`

**File:** `app/Console/Commands/SendReminder.php`

**Purpose:** Sends payment reminders to customers whose invoices are
unpaid and within a configurable reminder window (e.g. 3 days before
due date, on due date, and 3 days after due date).

**Usage:**

```bash
php artisan billing:send-reminders
```

**Behavior:**

1. Queries invoices where `status = 'unpaid'` and `due_date` falls

   within the configured reminder offsets.

2. Dispatches a `SendReminderNotification` job for each matching

   invoice.

3. Logs the number of reminders dispatched.

**Options:** None in the current implementation. Reminder offsets are
defined in code and may be externalized to config in a future release.

### `billing:generate-overdue`

**Purpose:** Marks invoices past their due date as `overdue` and
dispatches overdue notifications.

**Usage:**

```bash
php artisan billing:generate-overdue
```

### `billing:recalculate-balances`

**Purpose:** Recalculates customer outstanding balances from invoice and
payment records. Useful after data imports or corrections.

**Usage:**

```bash
php artisan billing:recalculate-balances
```

> **Note:** The exact command set may evolve. Run
> `php artisan list billing` to see the current commands registered
> under the `billing` namespace.

## Scheduled Jobs

The schedule is defined in `routes/console.php`. In production, a single
system cron entry invokes `php artisan schedule:run` every minute; the
scheduler itself decides which commands to execute.

| Schedule | Command | Description |
| --- | --- | --- |
| Daily at 08:00 | `billing:send-reminders` | Send due-date reminders |
| Daily at 00:00 | `billing:generate-overdue` | Transition unpaid invoices to overdue |
| Weekly on Monday 06:00 | `billing:recalculate-balances` | Reconcile customer balances |
| Daily at 03:00 | `queue:prune-batches` | Prune failed job batches older than 7 days |

Example `routes/console.php` snippet:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('billing:send-reminders')->dailyAt('08:00');
Schedule::command('billing:generate-overdue')->dailyAt('00:00');
Schedule::command('billing:recalculate-balances')->weeklyOn(1, '06:00');
```

## Running the Scheduler Locally

For development, use the foreground scheduler which runs every minute
and executes due commands immediately:

```bash
php artisan schedule:work
```

To see what the scheduler would run without executing anything:

```bash
php artisan schedule:list
```

## Testing Commands

Commands can be tested with Pest using the
`Illuminate\Foundation\Testing\PendingCommand` trait or by invoking the
command via `Artisan::call()`.

Example:

```php
it('sends reminders for invoices due in 3 days', function () {
    Invoice::factory()->create([
        'status' => 'unpaid',
        'due_date' => now()->addDays(3),
    ]);

    Artisan::call('billing:send-reminders');

    Notification::assertSentTo(Customer::first(), SendReminderNotification::class);
});
```

See [06-testing.md](06-testing.md) for the full testing guide.

## Adding a New Scheduled Command

1. Generate the command:

   ```bash
   php artisan make:command Billing\\MyNewCommand
   ```

2. Implement the command logic in `app/Console/Commands/MyNewCommand.php`.
3. Register the schedule entry in `routes/console.php`:

   ```php
   Schedule::command('billing:my-new-command')->dailyAt('02:00');
   ```

4. Write a feature test in

   `tests/Feature/Console/MyNewCommandTest.php`.

5. Document the command in this file.
