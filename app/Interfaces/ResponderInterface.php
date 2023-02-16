<?php

declare(strict_types=1);

namespace App\Interfaces;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

interface ResponderInterface
{
    public const INTERNAL_SERVER_ERROR_MESSAGE = 'Something went wrong.';

    public function response(array|object $data, int $statusCode = Response::HTTP_OK): Response;

    public function sendExceptionError(
        Throwable $e,
        array|object $data,
        int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR
    ): Response;
}
