<?php

declare(strict_types=1);

namespace Tests\Feature\Links;

use Tests\Helpers\LinkTestHelper;
use Tests\TestCase;

class GetLinkTest extends TestCase
{
    public function test_it_returns_a_404_response_when_the_link_does_not_exist(): void
    {
        $route = route('links.show', 0);

        $response = $this->getJson($route)->assertNotFound()->json('errors');

        $this->assertEquals('Link not found', $response['message']);
    }

    public function test_it_returns_the_link_resource_when_the_link_exists()
    {
        $link = LinkTestHelper::create();
        $linkResource = LinkTestHelper::getLinkResource($link);
        $route = route('links.show', $link->id);

        $response = $this->getJson($route)->assertOk()->json('data');

        $this->assertEquals($linkResource, $response);
    }
}
