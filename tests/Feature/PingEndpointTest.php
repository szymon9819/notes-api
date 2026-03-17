<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class PingEndpointTest extends TestCase
{
    public function test_ping_endpoint_returns_expected_response(): void
    {
        $response = $this->getJson('/api/ping');

        $response
            ->assertOk()
            ->assertExactJson([
                'status' => 'ok',
            ]);
    }
}
