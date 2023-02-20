<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\ResponderService;
use Exception;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use stdClass;
use Tests\TestCase;

class ResponderServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private ResponderService $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new ResponderService();
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
            ? '{"success":0,"code":500,"error":"The HTTP status code \"' . $statusCode . '\" is not valid."}'
            : '{"success":0,"code":500,"error":"Something went wrong."}';

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
            ? '{"success":0,"code":' . $expectedStatusCode . ',"error":"Test exception message","result":' . $expectedResult . '}'
            : '{"success":0,"code":' . $expectedStatusCode . ',"error":"Something went wrong.","result":' . $expectedResult . '}';

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
        $data = ['key' => 'value'];

        return [
            [1000, []],
            [0, new stdClass()],
            [-1, $this->getDummyObject()],
            [0, $data],
            [-1, $data],
        ];
    }

    /**
     * @return array With this item structure [
     *     $data: array|object,
     *     $expectedResult: string,
     *     $statusCode: int,
     *     $expectedStatusCode: int,
     */
    private function providesDataAndStatusCode(): array
    {
        $data = ['key' => 'value'];
        $expectedResult = '{"key":"value"}';

        return [
            [$data, $expectedResult, 12345, 500],
            [$data, $expectedResult, -1, 500],
            [$data, $expectedResult, 501, 501],
            [[], '[]', 500, 500],
            [new stdClass(), '{}', 400, 400],
            [$this->getDummyObject(), $expectedResult, 400, 400],
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
        $expectedResponse = '{"success":1,"key":"value"}';

        return [
            [[], '[]', 200, 200],
            [new stdClass(), '[]', 200, 200],
            [$data, $expectedResponse, 200, 200],
            [$data, $expectedResponse, 500, 500],
            [$this->getDummyObject(), $expectedResponse, 200, 200],
        ];
    }

    private function getDummyObject(): stdClass
    {
        $obj = new stdClass();
        $obj->key = 'value';

        return $obj;
    }
}
