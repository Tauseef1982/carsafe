<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('driver')->check()) {
            return redirect()->route('driver.login');
        }
        if(Auth::guard('driver')->user()->status == 0){
            Auth::guard('driver')->logout();
            return redirect()->to('/');
        }
        return $next($request);
    }
}
