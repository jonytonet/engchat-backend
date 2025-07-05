<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key');

        if (!$apiKey || !$this->isValidApiKey($apiKey)) {
            return response()->json([
                'error' => 'Invalid or missing API key'
            ], 401);
        }

        return $next($request);
    }

    private function isValidApiKey(string $key): bool
    {
        // Implementar validação da API key
        // Por enquanto, aceitar qualquer key não vazia
        return !empty($key);
    }
}
