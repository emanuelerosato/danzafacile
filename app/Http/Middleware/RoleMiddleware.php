<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $role
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!Auth::check()) {
            // Handle API vs Web authentication
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                    'error' => 'Authentication required'
                ], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if user is active
        if (!$user->active) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account deactivated',
                    'error' => 'Your account has been deactivated. Please contact support.'
                ], 403);
            }
            abort(403, 'Account deactivato. Contatta il supporto.');
        }

        // Super Admin può accedere ovunque
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Verifica il ruolo specifico
        $hasAccess = false;
        $errorMessage = 'Access denied';

        switch ($role) {
            case 'super_admin':
                $hasAccess = $user->isSuperAdmin();
                $errorMessage = 'Only Super Administrators can access this resource';
                break;
            
            case 'admin':
                $hasAccess = $user->isAdmin() || $user->isSuperAdmin();
                $errorMessage = 'Only Administrators can access this resource';
                break;
            
            case 'instructor':
                $hasAccess = $user->isInstructor() || $user->isAdmin() || $user->isSuperAdmin();
                $errorMessage = 'Only Instructors can access this resource';
                break;
            
            case 'student':
                $hasAccess = $user->isStudent() || $user->isInstructor() || $user->isAdmin() || $user->isSuperAdmin();
                $errorMessage = 'Student access required';
                break;
            
            default:
                $hasAccess = false;
                $errorMessage = 'Invalid role specified';
        }

        if (!$hasAccess) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'error' => 'Insufficient permissions'
                ], 403);
            }
            abort(403, $errorMessage);
        }

        return $next($request);
    }
}