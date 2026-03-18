<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AuthTokenEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_token_endpoint_returns_a_plain_text_token_for_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'alice@example.com',
        ]);

        $testResponse = $this->postJson(route('auth.login'), [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'Postman',
        ]);

        $testResponse
            ->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('expires_in', 3600);

        $token = $testResponse->json('token');
        $expiresAt = $testResponse->json('expires_at');

        $this->assertIsString($token);
        $this->assertNotSame('', $token);
        $this->assertIsString($expiresAt);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
            'name' => 'Postman',
        ]);
    }

    public function test_token_endpoint_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'alice@example.com',
        ]);

        $this->postJson(route('auth.login'), [
            'email' => 'alice@example.com',
            'password' => 'wrong-password',
            'device_name' => 'Postman',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_notes_and_tags_endpoints_require_authentication(): void
    {
        $this->getJson(route('notes.index'))->assertUnauthorized();
        $this->getJson(route('tags.index'))->assertUnauthorized();
        $this->postJson(route('notes.store'), [
            'title' => 'Protected',
            'status' => 'draft',
        ])->assertUnauthorized();
    }
}
