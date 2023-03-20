<?php

declare(strict_types=1);

namespace Tests\Feature\Links;

use Tests\Helpers\LinkTestHelper;
use Tests\TestCase;

class DeleteAllLinksTest extends TestCase
{
    public function test_it_returns_a_404_response_when_the_user_does_not_exist(): void
    {
        $route = route('links.destroy-all', 0);

        $response = $this->deleteJson($route)->assertNotFound()->json('errors');

        $this->assertEquals('User not found', $response['message']);
    }

    public function test_it_deletes_the_link_when_the_link_exists()
    {
        $link = LinkTestHelper::create();
        $route = route('links.destroy-all', $link->user->id);

        $response = $this->deleteJson($route)->assertOk()->json('data');

        $this->assertEquals('All links deleted successfully', $response['message']);
    }
}
