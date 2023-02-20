<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ResponderInterfaceException;
use App\Interfaces\ResponderInterface;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ResponderService implements ResponderInterface
{
    public function response(
        array|object $data = [],
        int $statusCode = Response::HTTP_OK,
        int $valueSuccessKey = ResponderInterface::VALUE_SUCCESS_TRUE
    ): Response {
        try {
            if (!$this->isValidHttpStatusCode($statusCode)) {
                throw new ResponderInterfaceException("The HTTP status code \"$statusCode\" is not valid.");
            }

            $resultData = $this->getResultData($data, $valueSuccessKey);

            return response($resultData, $statusCode);
        } catch (Throwable $e) {
            $errorData = [
                ResponderInterface::KEY_SUCCESS => ResponderInterface::VALUE_SUCCESS_FALSE,
                ResponderInterface::KEY_CODE => Response::HTTP_INTERNAL_SERVER_ERROR,
                ResponderInterface::KEY_ERROR => $this->getExceptionError($e),
            ];

            return response($errorData, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function sendExceptionError(
        Throwable $e,
        array|object $data,
        int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR
    ): Response {
        $errorCode = $this->isValidHttpStatusCode($statusCode) ? $statusCode : Response::HTTP_INTERNAL_SERVER_ERROR;
        $errorData = [
            ResponderInterface::KEY_CODE => $errorCode,
            ResponderInterface::KEY_ERROR => $this->getExceptionError($e),
            ResponderInterface::KEY_RESULT => $data,
        ];

        return $this->response($errorData, $errorCode, ResponderInterface::VALUE_SUCCESS_FALSE);
    }

    private function getExceptionError(Throwable $e): string
    {
        return isDevEnvironment() ? $e->getMessage() : self::INTERNAL_SERVER_ERROR_MESSAGE;
    }

    private function isValidHttpStatusCode(int $statusCode): bool
    {
        return in_array($statusCode, array_keys(Response::$statusTexts));
    }

    private function parseObjectToArray(object $obj): array
    {
        $json = json_encode($obj);

        return json_decode($json, true);
    }

    /**
     * @param object|array $data
     * @param int $valueSuccessKey
     * @return array|int[]|object
     */
    private function getResultData(object|array $data, int $valueSuccessKey): array|object
    {
        $resultData = [];

        if (is_object($data)) {
            $resultData = $this->parseObjectToArray($data);
        }

        if (is_array($data)) {
            $resultData = $data;
        }

        if (!empty($resultData)) {
            $resultData = [ResponderInterface::KEY_SUCCESS => $valueSuccessKey] + $resultData;
        }

        return $resultData;
    }
}
