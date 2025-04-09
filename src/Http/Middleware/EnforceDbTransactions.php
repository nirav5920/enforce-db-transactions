<?php

declare(strict_types=1);

namespace Nirav5920\EnforceDbTransactions\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class EnforceDbTransactions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldExcludePath($request)) {
            return $next($request);
        }

        $writeQueryCount = 0;
        $transactionStarted = false;

        // Track write operations and transaction state
        DB::listen(function ($query) use (&$writeQueryCount): void {
            $sql = strtolower($query->sql);
            if (preg_match('/\b(insert|update|delete|merge)\b/', $sql)) {
                $writeQueryCount++;
            }
        });

        DB::beforeExecuting(function () use (&$transactionStarted): void {
            if (DB::transactionLevel() > 0) {
                $transactionStarted = true;
            }
        });

        $response = $next($request);

        // Ensure transactions for write operations
        if ($writeQueryCount > 1 && ! $transactionStarted) {
            if (env('APP_ENV') === 'production') {
                Log::error([
                    'error' => 'Multiple write operations detected without a transaction. Please wrap your queries in a DB::transaction() block.',
                ]);
            } else {
                throw new RuntimeException(
                    'Multiple write operations detected without a transaction. Please wrap your queries in a DB::transaction() block.'
                );
            }
        }

        return $response;
    }

    /**
     * Determine if the request path should be excluded.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    private function shouldExcludePath(Request $request): bool
    {
        $path = $request->path();
        $excludedPaths = config('enforce-db-transactions.excluded_paths', []);

        foreach ($excludedPaths as $excludedPath) {
            if (str_contains($excludedPath, '*')) {
                $pattern = str_replace('*', '.*', $excludedPath);
                if (preg_match(sprintf('#^%s$#', $pattern), $path)) {
                    return true;
                }
            } elseif ($path === $excludedPath) {
                return true;
            }
        }

        return false;
    }
}
