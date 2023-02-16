<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ResponderInterfaceException;
use App\Interfaces\ResponderInterface;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class JsonResponderService implements ResponderInterface
{
    public function response(array|object $data = [], int $statusCode = Response::HTTP_OK): JsonResponse
    {
        try {
            if (!$this->isValidHttpStatusCode($statusCode)) {
                throw new ResponderInterfaceException("The HTTP status code \"$statusCode\" is not valid.");
            }

            return response()->json($data, $statusCode);
        } catch (Throwable $e) {
            $errorData = [
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'error' => $this->getExceptionError($e),
            ];

            return response()->json($errorData, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function sendExceptionError(
        Throwable $e,
        array|object $data,
        int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR
    ): JsonResponse {
        $errorCode = $this->isValidHttpStatusCode($statusCode) ? $statusCode : Response::HTTP_INTERNAL_SERVER_ERROR;
        $errorData = [
            'code' => $errorCode,
            'error' => $this->getExceptionError($e),
            'result' => $data,
        ];

        return $this->response($errorData, $errorCode);
    }

    private function getExceptionError(Throwable $e): string
    {
        return isDevEnvironment() ? $e->getMessage() : self::INTERNAL_SERVER_ERROR_MESSAGE;
    }

    private function isValidHttpStatusCode(int $statusCode): bool
    {
        return in_array($statusCode, array_keys(Response::$statusTexts));
    }
}
