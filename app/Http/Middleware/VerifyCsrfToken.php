<?php
namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Support\Facades\Log;

class VerifyCsrfToken extends Middleware
{
    protected function tokensMatch($request)
    {
        $match = parent::tokensMatch($request);
        Log::info('CSRF Token Match:', [
            'match' => $match,
            'token' => $request->header('X-CSRF-TOKEN'),
            'method' => $request->method(),
            'url' => $request->url()
        ]);
        return $match;
    }
}