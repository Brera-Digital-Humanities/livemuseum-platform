<?php

namespace Quivi\Profile\Classes;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$this->matchesPath($request)) {
            return $next($request);
        }

        $origin = (string) $request->headers->get('Origin');

        if ($request->isMethod('OPTIONS')) {
            return $this->preflightResponse($request, $origin);
        }

        $restoreOrigin = null;
        if ($origin !== '') {
            $restoreOrigin = $this->hideOriginFromDownstreamCors($request);
        }

        try {
            $response = $next($request);
        } finally {
            if ($restoreOrigin) {
                $restoreOrigin();
            }
        }

        if ($origin === '' || !$this->isOriginAllowed($origin)) {
            return $response;
        }

        return $this->addCorsHeaders($request, $response, $origin);
    }

    protected function preflightResponse(Request $request, string $origin): Response
    {
        if (!$this->isOriginAllowed($origin)) {
            return new Response('Origin not allowed.', 403);
        }

        return $this->addCorsHeaders($request, new Response('', 204), $origin);
    }

    protected function addCorsHeaders(Request $request, Response $response, string $origin): Response
    {
        if ($origin === '' || !$this->isOriginAllowed($origin)) {
            return $response;
        }

        $response->headers->set('Access-Control-Allow-Origin', $origin);
        $response->headers->set('Vary', $this->appendVary($response, 'Origin'));

        if ($this->supportsCredentials()) {
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        if ($request->isMethod('OPTIONS')) {
            $response->headers->set('Access-Control-Allow-Methods', implode(', ', $this->envList('CORS_ALLOWED_METHODS', [
                'GET',
                'POST',
                'PUT',
                'PATCH',
                'DELETE',
                'OPTIONS',
            ])));

            $allowedHeaders = $this->envList('CORS_ALLOWED_HEADERS', ['*']);
            $response->headers->set(
                'Access-Control-Allow-Headers',
                in_array('*', $allowedHeaders, true)
                    ? (string) $request->headers->get('Access-Control-Request-Headers', '*')
                    : implode(', ', $allowedHeaders)
            );

            $maxAge = (int) env('CORS_MAX_AGE', 0);
            if ($maxAge > 0) {
                $response->headers->set('Access-Control-Max-Age', (string) $maxAge);
            }
        }

        $exposedHeaders = $this->envList('CORS_EXPOSED_HEADERS', []);
        if ($exposedHeaders) {
            $response->headers->set('Access-Control-Expose-Headers', implode(', ', $exposedHeaders));
        }

        return $response;
    }

    protected function hideOriginFromDownstreamCors(Request $request): Closure
    {
        $originHeader = $request->headers->get('Origin');
        $serverOrigin = $request->server->get('HTTP_ORIGIN');

        $request->headers->remove('Origin');
        $request->server->remove('HTTP_ORIGIN');

        return function () use ($request, $originHeader, $serverOrigin) {
            if ($originHeader !== null) {
                $request->headers->set('Origin', $originHeader);
            }

            if ($serverOrigin !== null) {
                $request->server->set('HTTP_ORIGIN', $serverOrigin);
            }
        };
    }

    protected function isOriginAllowed(string $origin): bool
    {
        if ($origin === '') {
            return false;
        }

        $allowedOrigins = $this->envList('CORS_ALLOWED_ORIGINS', []);

        return in_array('*', $allowedOrigins, true) || in_array($origin, $allowedOrigins, true);
    }

    protected function matchesPath(Request $request): bool
    {
        foreach ($this->envList('CORS_PATHS', ['api/*']) as $path) {
            if ($request->is($path)) {
                return true;
            }
        }

        return false;
    }

    protected function supportsCredentials(): bool
    {
        return filter_var(env('CORS_SUPPORTS_CREDENTIALS', false), FILTER_VALIDATE_BOOLEAN);
    }

    protected function envList(string $key, array $default): array
    {
        $value = env($key);

        if ($value === null || trim((string) $value) === '') {
            return $default;
        }

        return array_values(array_filter(array_map('trim', explode(',', (string) $value)), 'strlen'));
    }

    protected function appendVary(Response $response, string $header): string
    {
        $vary = array_filter(array_map('trim', explode(',', (string) $response->headers->get('Vary'))));

        if (!in_array($header, $vary, true)) {
            $vary[] = $header;
        }

        return implode(', ', $vary);
    }
}
