<?php

declare(strict_types=1);

namespace Tests;

use App\Interfaces\ResponderInterface;
use App\Traits\ResponseAssertions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use ResponseAssertions;
    use RefreshDatabase;

    /** @param string[] $paths */
    public function getEndpoint(array $paths): string
    {
        return implode(ResponderInterface::SEPARATOR, $paths);
    }

    public function providesIdNotFound(): array
    {
        return [
            [-1],
            [0],
            [1],
        ];
    }
}
