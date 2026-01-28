<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Apikey;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-KEY');

    if (!$apiKey) {
        return response()->json(['error' => 'API key missing'], 401);
    }

    $key = Apikey::where('api_key', $apiKey)
        ->where('is_active', true)
        ->first();

    if (!$key) {
        return response()->json(['error' => 'Invalid API key'], 401);
    }

    // Attach account to request
    $request->merge(['account_id' => $key->account_id]);

    return $next($request);
    }
}
