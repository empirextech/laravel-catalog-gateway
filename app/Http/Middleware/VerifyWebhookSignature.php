<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyWebhookSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = (string) config('services.webhook.secret');
        $header = (string) $request->header('X-Signature', '');
        $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);

        if ($secret === '' || $header === '' || !hash_equals($expected, $header)) {
            abort(401, 'Invalid webhook signature.');
        }

        return $next($request);
    }
}
