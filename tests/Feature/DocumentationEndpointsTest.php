<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class DocumentationEndpointsTest extends TestCase
{
    public function test_api_docs_endpoint_redirects_to_scramble_ui(): void
    {
        $this->get('/api/docs')
            ->assertRedirectToRoute('scramble.docs.ui');
    }

    public function test_scramble_ui_endpoint_is_available(): void
    {
        $this->get('/docs/api')
            ->assertOk()
            ->assertSee('Notes API Demo');
    }

    public function test_openapi_document_endpoint_returns_specification(): void
    {
        $testResponse = $this->getJson('/docs/api.json');

        $testResponse
            ->assertOk()
            ->assertJsonPath('info.description', 'Demo REST API for managing notes and tags.');

        $document = $testResponse->json();

        $this->assertIsArray($document);
        $this->assertIsArray($document['paths'] ?? null);
        $this->assertArrayHasKey('/notes', $document['paths']);
        $this->assertArrayHasKey('/tags', $document['paths']);
    }
}
