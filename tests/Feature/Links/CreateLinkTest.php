<?php

declare(strict_types=1);

namespace Tests\Feature\Links;

use App\Models\Link;
use Tests\Helpers\LinkTestHelper;
use Tests\TestCase;

class CreateLinkTest extends TestCase
{
    public function test_it_returns_a_422_response_when_the_required_params_do_not_exist(): void
    {
        $route = route('links.store');

        $this->postJson($route)
            ->assertJsonValidationErrors('link')
            ->assertSeeText('The link field is required')
            ->assertUnprocessable();
    }

    public function test_it_returns_a_422_response_when_the_required_param_link_is_not_a_valid_url(): void
    {
        $params = ['link' => 'google'];

        $this->postJson(route('links.store', $params))
            ->assertJsonValidationErrors(['link'])
            ->assertSeeText('The link must be a valid URL')
            ->assertUnprocessable();
    }

    public function test_it_creates_the_link_when_the_required_params_meet_validation_rules()
    {
        $link = LinkTestHelper::make();
        $linkResource = LinkTestHelper::getLinkResource($link);

        $params = ['link' => $link->full_link];

        $response = $this->postJson(route('links.store', $params))->assertCreated()->json('data');

        $this->assertDatabaseCount('links', 1);
        $this->assertEquals($linkResource, $response);
    }

    public function test_it_returns_a_404_response_when_the_user_id_is_not_found(): void
    {
        $link = Link::factory()->make();
        $params = ['link' => $link->full_link];

        $this->postJson(route('links.store', $params))->assertNotFound();

        $this->assertDatabaseCount('links', 0);
    }
}
