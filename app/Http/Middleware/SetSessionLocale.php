<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetSessionLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->has('locale')) {
            app()->setLocale($request->session()->get('locale'));
        }

        return $next($request);
    }
}
