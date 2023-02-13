<?php

declare(strict_types=1);

namespace App\Interfaces;

use Symfony\Component\HttpFoundation\Response;

interface ResponderInterface
{
    public const INTERNAL_SERVER_ERROR_MESSAGE = 'Something went wrong.';

    public function mainResponse(array|object $data, int $statusCode);

    public function success(array|object $data, int $statusCode = Response::HTTP_OK);

    public function error(array|object $data, int $statusCode);

    public function created(array|object $data, int $statusCode);

    public function notFound(array|object $data, int $statusCode);
}
