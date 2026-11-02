<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class RequestTrace
{
    public function handle(Request $request, Closure $next): Response
    {
        // Never accept user-supplied request IDs as trusted log identifiers.
        $id = (string) Str::uuid();
        $request->attributes->set('carfolk_request_id', $id);

        // Laravel's normal exception reporting will inherit this safe context.
        // Do not log request bodies, contact details, session cookies or tokens.
        Log::withContext(['request_id' => $id]);

        $response = $next($request);
        $response->headers->set('X-Request-ID', $id);

        return $response;
    }
}
