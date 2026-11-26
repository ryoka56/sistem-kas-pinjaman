<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/** Pengukur sementara: menulis durasi app + query DB ke header Server-Timing. */
class Timing
{
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);
        $dbMs = 0.0;
        $count = 0;

        DB::listen(function ($query) use (&$dbMs, &$count) {
            $dbMs += $query->time;
            $count++;
        });

        $response = $next($request);

        $response->headers->set('Server-Timing', sprintf(
            'app;dur=%.0f, db;dur=%.0f, queries;desc="%d"',
            (microtime(true) - $start) * 1000,
            $dbMs,
            $count
        ));

        return $response;
    }
}
