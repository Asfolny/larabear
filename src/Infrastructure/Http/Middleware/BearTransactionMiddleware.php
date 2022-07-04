<?php

namespace GuardsmanPanda\Larabear\Infrastructure\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class BearTransactionMiddleware {
    public function handle(Request $request, Closure $next): Response {
        try {
            DB::beginTransaction();
            $res = $next($request);
            if ($res->getStatusCode() <= 400) {
                DB::commit();
            } else {
                DB::rollBack();
            }
            return $res;
        } catch (Throwable $t) {
            DB::rollBack();
            throw new RuntimeException("Transaction failed: [{$t->getMessage()}]", $t->getCode(), $t);
        }
    }
}
