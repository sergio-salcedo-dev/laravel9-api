<?php

declare(strict_types=1);

namespace App\Traits;

use App\Interfaces\ResponderInterface;
use Symfony\Component\HttpFoundation\Response;

trait ResponseAssertions
{
    protected function assertValidJsonAndContent(string $expectedResponse, Response $actualResponse): void
    {
        $this->assertJson($expectedResponse, $actualResponse->getContent());
        $this->assertSame($expectedResponse, $actualResponse->getContent());
    }

    protected function assertStatusCode(int $expectedStatusCode, Response $actualResponse): void
    {
        $this->assertEquals($expectedStatusCode, $actualResponse->getStatusCode());
    }

    protected function assertJsonResponseAndStatusCode(
        string $expectedResponse,
        int $expectedStatusCode,
        Response $actualResponse
    ): void {
        $this->assertStatusCode($expectedStatusCode, $actualResponse);
        $this->assertValidJsonAndContent($expectedResponse, $actualResponse);
    }

    public function assertSuccessKeyTrue(array $response): void
    {
        $this->assertEquals(ResponderInterface::VALUE_SUCCESS_TRUE, $response[ResponderInterface::KEY_SUCCESS]);
    }

    public function assertSuccessKeyFalse(array $response): void
    {
        $this->assertEquals(ResponderInterface::VALUE_SUCCESS_FALSE, $response[ResponderInterface::KEY_SUCCESS]);
    }

    public function assertMessageKey(string $message, array $response): void
    {
        $this->assertEquals($message, $response[ResponderInterface::KEY_MESSAGE]);
    }
}
