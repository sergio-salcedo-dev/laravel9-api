<?php

declare(strict_types=1);

namespace App\Interfaces;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

interface ResponderInterface
{
    public const INTERNAL_SERVER_ERROR_MESSAGE = 'Something went wrong.';
    public const KEY_SUCCESS = 'success';
    public const VALUE_SUCCESS_FALSE = 0;
    public const VALUE_SUCCESS_TRUE = 1;
    public const KEY_CODE = "code";
    public const KEY_ERROR = "error";
    public const KEY_RESULT = "result";
    public const KEY_MESSAGE = "message";
    const SEPARATOR = "/";

    public function response(
        array|object $data,
        int $statusCode = Response::HTTP_OK,
        int $valueSuccessKey = ResponderInterface::VALUE_SUCCESS_TRUE
    ): Response;

    public function sendExceptionError(
        Throwable $e,
        array|object $data,
        int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR
    ): Response;
}
