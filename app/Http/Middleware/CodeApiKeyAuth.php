<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;

class CodeApiKeyAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-API-KEY');

    if (!$key) {
        return response()->json(['error' => 'API key missing'], 401);
    }

    $apiKey = DB::table('qrcodeapis')->where('api_key', $key)
     ->where('is_active', true)->first();

    if (!$apiKey) {
        return response()->json(['error' => 'Invalid API key'], 401);
    }

    $request->merge(['account_id' => $apiKey->account_id]);
       return $next($request);
    }
}
