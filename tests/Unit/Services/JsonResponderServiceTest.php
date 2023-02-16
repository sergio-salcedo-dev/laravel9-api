<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\JsonResponderService;
use Exception;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use stdClass;
use Tests\TestCase;

class JsonResponderServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private JsonResponderService $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new JsonResponderService();
    }

    /** @dataProvider providesDataAndValidStatusCode */
    public function testResponse_withProvider_returnsExpectedJsonAndStatusCode(
        array|object $data,
        string $expectedResponse,
        int $statusCode,
        int $expectedStatusCode
    ): void {
        $response = $this->service->response($data, $statusCode);

        $this->assertJsonResponseAndStatusCode($expectedResponse, $expectedStatusCode, $response);
    }

    /** @dataProvider providesInvalidStatusCode */
    public function testResponseWithInvalidStatusCode(int $statusCode, array|object $data): void
    {
        $expectedResponse = isDevEnvironment()
            ? '{"code":500,"error":"The HTTP status code \"' . $statusCode . '\" is not valid."}'
            : '{"code":500,"error":"Something went wrong."}';

        $response = $this->service->response($data, $statusCode);

        $this->assertJsonResponseAndStatusCode($expectedResponse, 500, $response);
    }

    /** @dataProvider providesDataAndStatusCode */
    public function testSendExceptionError(
        array|object $data,
        string $expectedResult,
        int $statusCode,
        int $expectedStatusCode
    ): void {
        $exception = new Exception('Test exception message');
        $expectedResponse = isDevEnvironment()
            ? '{"code":' . $expectedStatusCode . ',"error":"Test exception message","result":' . $expectedResult . '}'
            : '{"code":' . $expectedStatusCode . ',"error":"Something went wrong.","result":' . $expectedResult . '}';

        $expectedResponse = '{"code":' . $expectedStatusCode . ',"error":"Test exception message","result":' . $expectedResult . '}';

        $response = $this->service->sendExceptionError($exception, $data, $statusCode);

        $this->assertJsonResponseAndStatusCode($expectedResponse, $expectedStatusCode, $response);
    }

    /**
     * @return array With this item structure [
     *     $data: array|object,
     *     $statusCode: int,
     */
    private function providesInvalidStatusCode(): array
    {
        return [
            [1000, []],
            [0, new stdClass()],
            [-1, $this->getDummyObject()],
            [0, ['key' => 'value']],
            [-1, ['key' => 'value']],
        ];
    }

    /**
     * @return array With this item structure [
     *     $data: array|object,
     *     $expectedResponse: string,
     *     $statusCode: int,
     *     $expectedStatusCode: int,
     */
    private function providesDataAndStatusCode(): array
    {
        return [
            [['key' => 'value'], '{"key":"value"}', 12345, 500],
            [['key' => 'value'], '{"key":"value"}', -1, 500],
            [['key' => 'value'], '{"key":"value"}', 501, 501],
            [[], '[]', 500, 500],
            [new stdClass(), '{}', 400, 400],
            [$this->getDummyObject(), '{"key":"value"}', 400, 400],
        ];
    }

    /**
     * @return array With this item structure [
     *     $data: array|object,
     *     $expectedResponse: string,
     *     $statusCode: int,
     *     $expectedStatusCode: int,
     */
    private function providesDataAndValidStatusCode(): array
    {
        $data = ['key' => 'value'];
        $expectedResponse = '{"key":"value"}';

        return [
            [[], '[]', 200, 200],
            [new stdClass(), '{}', 200, 200],
            [$data, $expectedResponse, 200, 200],
            [$data, $expectedResponse, 500, 500],
            [$this->getDummyObject(), '{"key":"value"}', 200, 200],
        ];
    }

    private function getDummyObject(): stdClass
    {
        $obj = new stdClass();
        $obj->key = 'value';

        return $obj;
    }
}
