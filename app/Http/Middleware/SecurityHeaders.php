<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    protected $unwantedHeaders = ['X-Powered-By', 'Server'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $csp = "default-src 'self';
        script-src 'self' 'unsafe-inline' https://kit.fontawesome.com;
        style-src 'self' 'unsafe-inline' https://fonts.bunny.net;
        img-src 'self' data: https:;
        connect-src 'self' https://ka-f.fontawesome.com;
        font-src 'self' https://fonts.bunny.net https://ka-f.fontawesome.com;
        frame-src 'self';
        frame-ancestors 'self'";

        $csp = trim(preg_replace('/\s\s+/', ' ', $csp));

        if (env('APP_ENV') === 'production') { // Using config instead of direct env()
            $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');
            $response->headers->set('X-Frame-Options', 'DENY');
            $response->headers->set('X-XSS-Protection', '1; mode=block');
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
            $response->headers->set('Content-Security-Policy', $csp);
            $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

            // Remove unwanted headers
            foreach ($this->unwantedHeaders as $header) {
                $response->headers->remove($header);
            }
        }

        return $response;
    }
}
