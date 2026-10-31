<?php
namespace App\Http\Middleware;
use App\Support\LeadAcceptance;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class EnsureLeadCollectionIsApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!LeadAcceptance::enabled()) {
            return response()->json(['message'=>'Customer enquiries are not currently available.'], 503);
        }
        return $next($request);
    }
}
