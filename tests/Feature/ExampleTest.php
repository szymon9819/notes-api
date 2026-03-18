<?php

declare(strict_types=1);

namespace Tests\Feature;

final class ExampleTest extends FeatureTestCase
{
    public function test_the_health_endpoint_returns_a_successful_response(): void
    {
        $testResponse = $this->get('/up');

        $testResponse->assertOk();
    }
}
