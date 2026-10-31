<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class EnsureLeadCollectionIsApproved {
  public function handle(Request $request, Closure $next): Response {
    if (!config('carfolk.leads_enabled') || !filter_var(config('carfolk.privacy_url'), FILTER_VALIDATE_URL)) {
      return response()->json(['message'=>'Customer enquiries are not currently available.'],503);
    }
    return $next($request);
  }
}
