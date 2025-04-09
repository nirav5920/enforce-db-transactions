<?php

declare(strict_types=1);

namespace Nirav5920\EnforceDbTransactions;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use Nirav5920\EnforceDbTransactions\Http\Middleware\EnforceDbTransactions;

class EnforceDbTransactionsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/enforce-db-transactions.php',
            'enforce-db-transactions'
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/enforce-db-transactions.php' => config_path('enforce-db-transactions.php'),
            ], 'config');
        }

        $this->loadMiddleware();
    }

    /**
     * Load middleware.
     *
     * @return void
     */
    protected function loadMiddleware(): void
    {
        $kernel = $this->app->make(Kernel::class);
        $kernel->appendMiddlewareToGroup('web', EnforceDbTransactions::class);
        $kernel->appendMiddlewareToGroup('api', EnforceDbTransactions::class);
    }
}
