<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class DocumentationEndpointsTest extends TestCase
{
    public function test_api_docs_endpoint_redirects_to_scramble_ui(): void
    {
        $this->get(route('docs.show'))
            ->assertRedirectToRoute('scramble.docs.ui');
    }

    public function test_scramble_ui_endpoint_is_available(): void
    {
        $this->get(route('scramble.docs.ui'))
            ->assertOk()
            ->assertSee('Notes API Demo');
    }

    public function test_openapi_document_endpoint_returns_specification(): void
    {
        $testResponse = $this->getJson(route('scramble.docs.document'));

        $testResponse
            ->assertOk()
            ->assertJsonPath('info.description', 'Demo REST API for managing notes and tags.');

        $document = $testResponse->json();

        $this->assertIsArray($document);
        $this->assertIsArray($document['paths'] ?? null);
        $this->assertArrayHasKey('/login', $document['paths']);
        $this->assertArrayHasKey('/notes', $document['paths']);
        $this->assertArrayHasKey('/tags', $document['paths']);
    }
}
