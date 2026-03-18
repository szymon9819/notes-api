<?php

declare(strict_types=1);

namespace Tests\Feature;

final class PingEndpointTest extends FeatureTestCase
{
    public function test_ping_endpoint_returns_expected_response(): void
    {
        $testResponse = $this->getJson(route('system.ping'));

        $testResponse
            ->assertOk()
            ->assertExactJson([
                'status' => 'ok',
            ]);
    }
}
