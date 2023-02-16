<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait JsonResponseAssertions
{
    protected function assertValidJsonAndContent(string $expectedResponse, JsonResponse $actualResponse): void
    {
        $this->assertEquals(json_decode($expectedResponse, true), $actualResponse->getData(true));
        $this->assertEquals(json_decode($expectedResponse), $actualResponse->getData());
        $this->assertJson($expectedResponse, $actualResponse->getContent());
        $this->assertSame($expectedResponse, $actualResponse->getContent());
    }

    protected function assertStatusCode(int $expectedStatusCode, JsonResponse $actualResponse): void
    {
        $this->assertEquals($expectedStatusCode, $actualResponse->getStatusCode());
    }

    protected function assertJsonResponseAndStatusCode(
        string $expectedResponse,
        int $expectedStatusCode,
        JsonResponse $actualResponse
    ): void {
        $this->assertInstanceOf(JsonResponse::class, $actualResponse);
        $this->assertStatusCode($expectedStatusCode, $actualResponse);
        $this->assertValidJsonAndContent($expectedResponse, $actualResponse);
    }
}
