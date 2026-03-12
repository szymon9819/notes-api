<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class ExampleTest extends TestCase
{
    public function test_the_health_endpoint_returns_a_successful_response(): void
    {
        $testResponse = $this->get('/up');

        $testResponse->assertOk();
    }
}
