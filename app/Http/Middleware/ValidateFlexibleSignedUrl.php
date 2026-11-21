<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ValidateFlexibleSignedUrl
{
    /**
     * Accept signed SMS links even when localhost vs 127.0.0.1 (or APP_URL) differs.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->hasValidSignature($request)) {
            return $next($request);
        }

        abort(403, 'Invalid signature.');
    }

    private function hasValidSignature(Request $request): bool
    {
        if ($request->hasValidSignature()) {
            return true;
        }

        $signature = (string) $request->query('signature', '');
        if ($signature === '') {
            return false;
        }

        $queryString = (new Collection(explode('&', (string) $request->server->get('QUERY_STRING'))))
            ->reject(fn (string $parameter) => Str::before($parameter, '=') === 'signature')
            ->join('&');

        $path = '/'.$request->path();
        $keys = [(string) config('app.key')];
        $bases = $this->candidateUrlBases($request);

        foreach ($bases as $base) {
            $original = rtrim($base.$path.'?'.$queryString, '?');
            foreach ($keys as $key) {
                if (hash_equals(hash_hmac('sha256', $original, $key), $signature)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function candidateUrlBases(Request $request): array
    {
        $scheme = $request->getScheme();
        $port = $request->getPort();
        $portSuffix = ($port && ! in_array($port, [80, 443], true)) ? ':'.$port : '';

        $hosts = array_filter(array_unique([
            $request->getHost(),
            parse_url((string) config('app.url'), PHP_URL_HOST),
            'localhost',
            '127.0.0.1',
            'erp.dunwassip.com',
        ]));

        $bases = [];
        foreach ($hosts as $host) {
            $bases[] = $scheme.'://'.$host.$portSuffix;
        }

        $appUrl = rtrim((string) config('app.url'), '/');
        if ($appUrl !== '') {
            $bases[] = $appUrl;
        }

        $bases[] = 'http://localhost:8000';
        $bases[] = 'http://127.0.0.1:8000';
        $bases[] = 'https://erp.dunwassip.com';

        return array_values(array_unique($bases));
    }
}
