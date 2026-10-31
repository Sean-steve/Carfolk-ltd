<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class EnsureDealershipAdmin {
    public function handle(Request $request, Closure $next): Response {
        if (! $request->user()?->is_admin || ! $request->user()?->is_active) {
            abort(403, 'Dealership administrator access required.');
        }
        return $next($request);
    }
}
