<?php

declare(strict_types=1);

namespace Tests\Feature\Links;

use Tests\Helpers\LinkTestHelper;
use Tests\TestCase;

class UpdateLinkTest extends TestCase
{
    public function test_it_returns_a_422_response_when_the_required_params_do_not_exist(): void
    {
        $link = LinkTestHelper::create();
        $route = route('links.update', $link);

        $this->putJson($route)
            ->assertJsonValidationErrors(['short_link', 'full_link'])
            ->assertSeeText('The short link field is required')
            ->assertSeeText('The full link field is required')
            ->assertUnprocessable();
    }

    public function test_it_returns_a_422_response_when_the_required_param_full_link_is_not_a_valid_url(): void
    {
        $link = LinkTestHelper::create();
        $params = [
            'short_link' => 'test.com',
            'full_link' => 'test',
            'link' => $link->id,
        ];

        $route = route('links.update', $params);

        $this->putJson($route)
            ->assertJsonValidationErrors(['full_link'])
            ->assertSeeText('The full link must be a valid URL')
            ->assertJsonMissingValidationErrors(['short_link'])
            ->assertUnprocessable();
    }

    public function test_it_returns_a_404_response_when_the_link_is_not_found(): void
    {
        $route = route('links.update', ['link' => 0]);

        $response = $this->putJson($route)->assertNotFound()->json('errors');

        $this->assertEquals('Link not found', $response['message']);
    }

    public function test_it_updates_the_link_when_the_required_params_meet_validation_rules()
    {
        $link = LinkTestHelper::create();
        $expectedLink = LinkTestHelper::make([
            'id' => $link->id,
            'short_link' => 'google.com',
            'full_link' => 'https://google.com',
            'created_at' => $link->created_at,
        ], $link->user);
        $updatedLinkResource = LinkTestHelper::getLinkResource($expectedLink);
        $params = [
            'short_link' => $expectedLink->short_link,
            'full_link' => $expectedLink->full_link,
            'link' => $link->id,
        ];

        $route = route('links.update', $params);

        $response = $this->putJson($route)->assertOk()->json('data');

        $this->assertEquals($updatedLinkResource, $response);
    }
}
