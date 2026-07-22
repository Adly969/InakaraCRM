<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AutoLoginDevUser
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('local')) {
            if (Auth::check()) {
                $request->session()->forget('explicit_logout');
            }

            if ($request->session()->has('explicit_logout')) {
                return $next($request);
            }

            if (Auth::guest()) {
                $user = User::where('email', 'owner@inakara.com')->first();

                if ($user) {
                    Auth::login($user);
                }
            }
        }

        return $next($request);
    }
}
