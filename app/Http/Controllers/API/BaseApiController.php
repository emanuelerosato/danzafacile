<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Base API Controller
 *
 * Provides common functionality for all API controllers including:
 * - Standardized JSON responses
 * - Error handling
 * - Pagination helpers
 * - Multi-tenant security helpers
 */
class BaseApiController extends Controller
{
    /**
     * Return success response with data
     */
    protected function successResponse($data = null, string $message = 'Operation completed successfully', int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => [
                'timestamp' => now()->toISOString(),
                'version' => '1.0',
            ]
        ];

        return response()->json($response, $statusCode);
    }

    /**
     * Return paginated success response
     */
    protected function paginatedResponse($paginatedData, string $message = 'Data retrieved successfully'): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $paginatedData->items(),
            'meta' => [
                'pagination' => [
                    'current_page' => $paginatedData->currentPage(),
                    'per_page' => $paginatedData->perPage(),
                    'total' => $paginatedData->total(),
                    'last_page' => $paginatedData->lastPage(),
                    'from' => $paginatedData->firstItem(),
                    'to' => $paginatedData->lastItem(),
                    'has_more_pages' => $paginatedData->hasMorePages(),
                ],
                'timestamp' => now()->toISOString(),
                'version' => '1.0',
            ]
        ];

        return response()->json($response, 200);
    }

    /**
     * Return error response
     */
    protected function errorResponse(string $message = 'An error occurred', int $statusCode = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'data' => null,
            'meta' => [
                'timestamp' => now()->toISOString(),
                'version' => '1.0',
            ]
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return validation error response
     */
    protected function validationErrorResponse($validator): JsonResponse
    {
        return $this->errorResponse(
            'Validation failed',
            422,
            $validator->errors()
        );
    }

    /**
     * Return not found response
     */
    protected function notFoundResponse(string $resource = 'Resource'): JsonResponse
    {
        return $this->errorResponse(
            $resource . ' not found',
            404
        );
    }

    /**
     * Return unauthorized response
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized access'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }

    /**
     * Return forbidden response
     */
    protected function forbiddenResponse(string $message = 'Forbidden access'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * Get authenticated user
     */
    protected function getAuthenticatedUser()
    {
        return auth()->user();
    }

    /**
     * Get user's school (for multi-tenant security)
     */
    protected function getUserSchool()
    {
        $user = $this->getAuthenticatedUser();
        return $user ? $user->school : null;
    }

    /**
     * Validate multi-tenant access for a resource
     */
    protected function validateTenantAccess($resource, string $foreignKey = 'school_id'): bool
    {
        $userSchool = $this->getUserSchool();

        if (!$userSchool) {
            return false;
        }

        if (is_object($resource) && property_exists($resource, $foreignKey)) {
            return $resource->{$foreignKey} === $userSchool->id;
        }

        if (is_array($resource) && isset($resource[$foreignKey])) {
            return $resource[$foreignKey] === $userSchool->id;
        }

        return false;
    }

    /**
     * Apply multi-tenant scope to query builder
     */
    protected function scopeToUserSchool($query, string $foreignKey = 'school_id')
    {
        $userSchool = $this->getUserSchool();

        if ($userSchool) {
            return $query->where($foreignKey, $userSchool->id);
        }

        // If no school, return empty result set for security
        return $query->where('id', -1);
    }

    /**
     * Get pagination parameters from request
     */
    protected function getPaginationParams(Request $request): array
    {
        return [
            'per_page' => min($request->get('per_page', 15), 100), // Max 100 items per page
            'page' => $request->get('page', 1),
        ];
    }

    /**
     * Get sorting parameters from request
     */
    protected function getSortingParams(Request $request, string $defaultSort = 'created_at', string $defaultOrder = 'desc'): array
    {
        $allowedSorts = ['id', 'created_at', 'updated_at', 'name', 'title']; // Override in child controllers
        $allowedOrders = ['asc', 'desc'];

        $sort = $request->get('sort', $defaultSort);
        $order = $request->get('order', $defaultOrder);

        return [
            'sort' => in_array($sort, $allowedSorts) ? $sort : $defaultSort,
            'order' => in_array($order, $allowedOrders) ? $order : $defaultOrder,
        ];
    }

    /**
     * Apply filters to query from request
     */
    protected function applyFilters($query, Request $request, array $filterableFields = []): object
    {
        foreach ($filterableFields as $field) {
            if ($request->has($field) && $request->get($field) !== null) {
                $value = $request->get($field);

                // Handle different filter types
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, 'like', '%' . $value . '%');
                }
            }
        }

        return $query;
    }

    /**
     * Handle file upload with validation
     */
    protected function handleFileUpload(Request $request, string $fieldName, string $disk = 'public', array $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf']): ?string
    {
        if (!$request->hasFile($fieldName)) {
            return null;
        }

        $file = $request->file($fieldName);

        // Validate file type
        $extension = $file->getClientOriginalExtension();
        if (!in_array(strtolower($extension), $allowedTypes)) {
            throw new \InvalidArgumentException('File type not allowed');
        }

        // Store file
        return $file->store('uploads/' . date('Y/m'), $disk);
    }
}