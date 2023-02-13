<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\ResponderInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ApiResponderService implements ResponderInterface
{
    public function mainResponse(array|object $data, int $statusCode): JsonResponse
    {
        return response()->json($data, $statusCode);
    }

    public function success(array|object $data, int $statusCode = Response::HTTP_OK): JsonResponse
    {
        return $this->mainResponse($data, $statusCode);
    }

    public function error(array|object $data, int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        return $this->mainResponse($data, $statusCode);
    }

    public function sendError500(Throwable|Exception $e): JsonResponse
    {
        return $this->error([
            'error' => self::INTERNAL_SERVER_ERROR_MESSAGE,
            'message' => $e->getMessage(),
        ]);
    }

    public function created(array|object $data, int $statusCode = Response::HTTP_CREATED): JsonResponse
    {
        return $this->mainResponse($data, $statusCode);
    }

    public function notFound(array|object $data, int $statusCode = Response::HTTP_NOT_FOUND): JsonResponse
    {
        return $this->mainResponse($data, $statusCode);
    }
}
