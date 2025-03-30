# Enforce DB Transactions

A Laravel middleware that automatically detects and enforces database transactions for multiple write operations within a single request.

## Problem Statement

In Laravel applications, when multiple write operations (INSERT, UPDATE, DELETE) are performed in a single request, they should be wrapped in a database transaction to ensure data integrity. However, developers often forget to do this, leading to potential data inconsistencies.

This package helps enforce good database practices by:

1. Detecting requests with multiple write operations
2. Logging errors in production/staging when transactions are missing
3. Throwing exceptions in development to alert developers

## Installation

You can install the package via composer:

```bash
composer require nirav5920/enforce-db-transactions
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Nirav5920\EnforceDbTransactions\EnforceDbTransactionsServiceProvider" --tag="config"
```

This will create a `config/enforce-db-transactions.php` file where you can customize the excluded paths:

```php
return [
    'excluded_paths' => [
        'api/health-check',
        '_debugbar/*',
        'telescope/*',
        'horizon/*',
        'api/pos/login',
        // Add your custom excluded paths here
    ],
];
```

## Usage

The middleware is automatically registered to both the `web` and `api` middleware groups.

If you prefer to manually register the middleware, you can remove it from the `web` and `api` groups in the configuration and add it to your route definitions:

```php
// In app/Http/Kernel.php
protected $routeMiddleware = [
    // ...
    'enforce.transactions' => \Nirav5920\EnforceDbTransactions\Middleware\EnforceDbTransactions::class,
];

// In your routes file
Route::middleware('enforce.transactions')->group(function () {
    // Your routes here
});
```

## How It Works

The middleware:

1. Listens for database queries being executed
2. Counts the number of write operations (INSERT, UPDATE, DELETE, MERGE)
3. Checks if a transaction has been started
4. If multiple write operations are detected without a transaction:
   - In production/staging: Logs an error
   - In development: Throws an exception

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.