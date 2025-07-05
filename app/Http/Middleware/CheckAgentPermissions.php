<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAgentPermissions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission = null): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // Verificar se é agente
        if (!$user->hasRole('agent') && !$user->hasRole('admin')) {
            return response()->json(['error' => 'Insufficient permissions'], 403);
        }

        // Verificar permissão específica se fornecida
        if ($permission && !$user->can($permission)) {
            return response()->json(['error' => 'Permission denied'], 403);
        }

        return $next($request);
    }
}
