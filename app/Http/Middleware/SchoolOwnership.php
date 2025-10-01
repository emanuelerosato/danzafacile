<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolOwnership
{
    /**
     * Handle an incoming request to ensure school data isolation.
     * This middleware ensures that admins can only access data belonging to their school.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
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

        // Super Admin can access all schools' data
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Admin must have a school assigned
        if ($user->isAdmin() && !$user->school_id) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No school assigned',
                    'error' => 'Your account is not associated with any school. Please contact support.'
                ], 403);
            }
            abort(403, 'Account non associato a nessuna scuola. Contatta il supporto.');
        }

        // Students must have a school assigned
        if ($user->isStudent() && !$user->school_id) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No school assigned',
                    'error' => 'Your account is not associated with any school. Please contact support.'
                ], 403);
            }
            abort(403, 'Account non associato a nessuna scuola. Contatta il supporto.');
        }

        // Check route model binding for school ownership
        $this->checkRouteModelOwnership($request, $user);

        return $next($request);
    }

    /**
     * Check if the user has access to the resource in the route.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return void
     */
    private function checkRouteModelOwnership(Request $request, $user)
    {
        // Skip ownership check for Super Admin
        if ($user->isSuperAdmin()) {
            return;
        }

        $route = $request->route();
        if (!$route) {
            return;
        }

        $parameters = $route->parameters();

        foreach ($parameters as $key => $model) {
            if (is_object($model)) {
                $this->validateModelOwnership($model, $user, $request);
            }
        }
    }

    /**
     * Validate that the user owns or has access to the given model.
     *
     * @param  mixed  $model
     * @param  \App\Models\User  $user
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    private function validateModelOwnership($model, $user, $request)
    {
        $modelClass = get_class($model);

        switch ($modelClass) {
            case 'App\Models\School':
                // Admin can only access their own school
                if ($user->isAdmin() && $model->id !== $user->school_id) {
                    $this->denyAccess($request, 'School access denied');
                }
                break;

            case 'App\Models\Course':
                // Admin and Student can only access courses from their school
                if (($user->isAdmin() || $user->isStudent()) && $model->school_id !== $user->school_id) {
                    $this->denyAccess($request, 'Course access denied');
                }
                break;

            case 'App\Models\User':
                // Admin can only access users from their school (except themselves)
                if ($user->isAdmin() && $model->id !== $user->id && $model->school_id !== $user->school_id) {
                    $this->denyAccess($request, 'User access denied');
                }
                // Student can only access their own profile
                if ($user->isStudent() && $model->id !== $user->id) {
                    $this->denyAccess($request, 'User access denied');
                }
                break;

            case 'App\Models\CourseEnrollment':
                // Admin can only access enrollments from their school
                if ($user->isAdmin() && $model->course->school_id !== $user->school_id) {
                    $this->denyAccess($request, 'Enrollment access denied');
                }
                // Student can only access their own enrollments
                if ($user->isStudent() && $model->user_id !== $user->id) {
                    $this->denyAccess($request, 'Enrollment access denied');
                }
                break;

            case 'App\Models\Payment':
                // Admin can only access payments from their school
                if ($user->isAdmin() && $model->school_id !== $user->school_id) {
                    $this->denyAccess($request, 'Payment access denied');
                }
                // Student can only access their own payments
                if ($user->isStudent() && $model->user_id !== $user->id) {
                    $this->denyAccess($request, 'Payment access denied');
                }
                break;

            case 'App\Models\Document':
                // Admin can only access documents from their school
                if ($user->isAdmin() && $model->user->school_id !== $user->school_id) {
                    $this->denyAccess($request, 'Document access denied');
                }
                // Student can only access their own documents
                if ($user->isStudent() && $model->user_id !== $user->id) {
                    $this->denyAccess($request, 'Document access denied');
                }
                break;

            case 'App\Models\MediaItem':
                // Admin can only access media from their school
                if ($user->isAdmin() && $model->school_id !== $user->school_id) {
                    $this->denyAccess($request, 'Media access denied');
                }
                // Student can only access media from their school
                if ($user->isStudent() && $model->school_id !== $user->school_id) {
                    $this->denyAccess($request, 'Media access denied');
                }
                break;

            case 'App\Models\Event':
                // Admin can only access events from their school
                if ($user->isAdmin() && $model->school_id !== $user->school_id) {
                    $this->denyAccess($request, 'Event access denied');
                }
                // Student can only access events from their school
                if ($user->isStudent() && $model->school_id !== $user->school_id) {
                    $this->denyAccess($request, 'Event access denied');
                }
                break;

            case 'App\Models\EventRegistration':
                // Admin can only access event registrations from their school
                if ($user->isAdmin() && $model->event->school_id !== $user->school_id) {
                    $this->denyAccess($request, 'Event registration access denied');
                }
                // Student can only access their own event registrations
                if ($user->isStudent() && $model->user_id !== $user->id) {
                    $this->denyAccess($request, 'Event registration access denied');
                }
                break;

            case 'App\Models\Staff':
                // Admin can only access staff from their school
                if ($user->isAdmin() && $model->user->school_id !== $user->school_id) {
                    $this->denyAccess($request, 'Staff access denied');
                }
                break;

            case 'App\Models\StaffSchedule':
                // Admin can only access staff schedules from their school
                if ($user->isAdmin() && $model->staff->user->school_id !== $user->school_id) {
                    $this->denyAccess($request, 'Staff schedule access denied');
                }
                break;

            case 'App\Models\Attendance':
                // Admin can only access attendance from their school
                if ($user->isAdmin() && $model->user->school_id !== $user->school_id) {
                    $this->denyAccess($request, 'Attendance access denied');
                }
                // Student can only access their own attendance
                if ($user->isStudent() && $model->user_id !== $user->id) {
                    $this->denyAccess($request, 'Attendance access denied');
                }
                break;

            case 'App\Models\MediaGallery':
                // Admin can only access media galleries from their school
                if ($user->isAdmin() && $model->school_id !== $user->school_id) {
                    $this->denyAccess($request, 'Media gallery access denied');
                }
                // Student can only access public galleries from their school
                if ($user->isStudent() && ($model->school_id !== $user->school_id || !$model->is_public)) {
                    $this->denyAccess($request, 'Media gallery access denied');
                }
                break;

            case 'App\Models\Ticket':
                // Admin can only access tickets from users in their school
                if ($user->isAdmin() && $model->user->school_id !== $user->school_id) {
                    $this->denyAccess($request, 'Ticket access denied');
                }
                // Student can only access their own tickets
                if ($user->isStudent() && $model->user_id !== $user->id) {
                    $this->denyAccess($request, 'Ticket access denied');
                }
                break;
        }
    }

    /**
     * Deny access with appropriate response format.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $message
     * @return void
     */
    private function denyAccess($request, $message = 'Access denied')
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            response()->json([
                'success' => false,
                'message' => $message,
                'error' => 'You do not have permission to access this resource'
            ], 403)->throwResponse();
        }
        
        abort(403, $message);
    }
}