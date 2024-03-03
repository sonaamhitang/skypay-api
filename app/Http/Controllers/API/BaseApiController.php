<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BaseApiController extends Controller
{
    /**
     * Return a success JSON response.
     *
     * @param mixed $data
     * @param string|null $message
     * @param int $code
     * @return JsonResponse
     */
    protected function success($data, string $message = null, int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message ?? "Success",
            'data' => $data,
        ], $code);
    }

    /**
     * Return an error JSON response.
     *
     * @param string|null $message
     * @param int $code
     * @param array|null $errors
     * @return JsonResponse
     */
    protected function error(string $message = null, $errors = null): JsonResponse
    {
        $response = [
            'status' => false,
            'message' => $message ?? "An error occurred",
        ];

        if (!is_null($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, 500);
    }

    /**
     * Return a not found JSON response.
     *
     * @param string|null $message
     * @return JsonResponse
     */
    protected function notFound(string $message = null): JsonResponse
    {
        return $this->error($message ?? "Resource not found", 404);
    }

    /**
     * Return an unauthorized JSON response.
     *
     * @param string|null $message
     * @return JsonResponse
     */
    protected function unauthorized(string $message = null): JsonResponse
    {
        return $this->error($message ?? "Unauthorized access", 401);
    }

    /**
     * Return a validation error JSON response.
     *
     * @param array $errors
     * @param string|null $message
     * @return JsonResponse
     */
    protected function validationError(array $errors, string $message = null): JsonResponse
    {
        return $this->error($message ?? "Validation errors", 422, $errors);
    }

    /**
     * Return a forbidden JSON response.
     *
     * @param string|null $message
     * @return JsonResponse
     */
    protected function forbidden(string $message = null): JsonResponse
    {
        return $this->error($message ?? "Access to this resource is forbidden", 403);
    }

    /**
     * Return a too many requests JSON response.
     *
     * @param string|null $message
     * @return JsonResponse
     */
    protected function tooManyRequests(string $message = null): JsonResponse
    {
        return $this->error($message ?? "Too many requests", 429);
    }

    /**
     * Return an internal server error JSON response.
     *
     * @param string|null $message
     * @return JsonResponse
     */
    protected function serverError(string $message = null): JsonResponse
    {
        return $this->error($message ?? "Internal Server Error", 500);
    }
}
