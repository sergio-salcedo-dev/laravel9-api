<?php

declare(strict_types=1);

namespace Tests;

use App\Traits\JsonResponseAssertions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use JsonResponseAssertions;
}
