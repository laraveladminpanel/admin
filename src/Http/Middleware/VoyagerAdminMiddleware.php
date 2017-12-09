<?php

namespace LaravelAdminPanel\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use LaravelAdminPanel\Facades\Admin;

class AdminAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!Auth::guest()) {
            $user = Admin::model('User')->find(Auth::id());

            return $user->hasPermission('browse_admin') ? $next($request) : redirect('/');
        }

        $urlLogin = route('admin.login');
        $urlIntended = $request->url();
        if ($urlIntended == $urlLogin) {
            $urlIntended = null;
        }

        return redirect($urlLogin)->with('url.intended', $urlIntended);
    }
}
