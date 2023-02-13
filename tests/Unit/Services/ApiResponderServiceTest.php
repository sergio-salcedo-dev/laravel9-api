<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\ApiResponderService;
use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ApiResponderServiceTest extends TestCase
{
    protected ApiResponderService $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new ApiResponderService();
    }

    public function testMainResponse(): void
    {
        $data = ['name' => 'John Doe'];
        $statusCode = Response::HTTP_OK;

        $response = $this->service->mainResponse($data, $statusCode);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(json_encode($data), $response->getContent());
        $this->assertEquals($statusCode, $response->getStatusCode());
    }

    public function testSuccessResponse(): void
    {
        $data = ['name' => 'John Doe'];
        $statusCode = Response::HTTP_OK;

        $response = $this->service->success($data, $statusCode);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(json_encode($data), $response->getContent());
        $this->assertEquals($statusCode, $response->getStatusCode());
    }

    public function testErrorResponse(): void
    {
        $data = ['error' => 'Something went wrong'];
        $statusCode = 500;

        $response = $this->service->error($data, $statusCode);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(json_encode($data), $response->getContent());
        $this->assertEquals($statusCode, $response->getStatusCode());
    }

    public function testSendError500(): void
    {
        $e = new Exception('Some error message');

        $response = $this->service->sendError500($e);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $expectedData = [
            'error' => 'Something went wrong.',
            'message' => $e->getMessage(),
        ];
        $this->assertEquals(json_encode($expectedData), $response->getContent());
        $this->assertEquals(500, $response->getStatusCode());
    }

    public function testCreated_returns_a_created_response(): void
    {
        $data = ['message' => 'Resource created successfully'];
        $statusCode = 201;

        $response = $this->service->created($data, $statusCode);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($statusCode, $response->getStatusCode());
        $this->assertEquals(['message' => 'Resource created successfully'], $response->getData(true));
    }

    public function testNotFound_returns_a_not_found_response(): void
    {
        $data = ['message' => 'Resource not found'];
        $statusCode = 404;

        $response = $this->service->notFound($data, $statusCode);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals(['message' => 'Resource not found'], $response->getData(true));
    }
}
