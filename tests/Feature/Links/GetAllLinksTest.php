<?php

declare(strict_types=1);

namespace Tests\Feature\Links;

use Laravel\Sanctum\Sanctum;
use Tests\Helpers\LinkTestHelper;
use Tests\Helpers\UserTestHelper;
use Tests\TestCase;

class GetAllLinksTest extends TestCase
{
    public function test_fetch_all_links_without_create_any_link_returns_empty_array(): void
    {
        Sanctum::actingAs(UserTestHelper::create());

        $response = $this->getJson(route('links.index'))->assertOk()->json('data');

        $this->assertEmpty($response);
    }

    public function test_fetch_all_links_with_one_link_created(): void
    {
        $link = LinkTestHelper::create();
        Sanctum::actingAs($link->user);
        $linkResource = LinkTestHelper::getLinkResource($link);

        $response = $this->getJson(route('links.index'))->assertOk()->json('data');

        $this->assertCount(1, $response);
        $this->assertEquals($linkResource, $response[0]);
    }

    public function test_fetch_all_links_with_links_created(): void
    {
        $link1 = LinkTestHelper::create(['full_link' => 'http://google.com']);
        Sanctum::actingAs($link1->user);
        $linkResource1 = LinkTestHelper::getLinkResource($link1);

        $link2 = LinkTestHelper::create();
        $linkResource2 = LinkTestHelper::getLinkResource($link2);

        $response = $this->getJson(route('links.index'))->assertOk()->json('data');

        $this->assertCount(2, $response);
        $this->assertEquals($linkResource1, $response[0]);
        $this->assertEquals($linkResource2, $response[1]);
    }
}
