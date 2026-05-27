<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Services\AIServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class NoteApiTest extends TestCase
{
    use RefreshDatabase;

    protected $mockAIService;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock AIServiceInterface to prevent outbound HTTP requests to external APIs
        $this->mockAIService = Mockery::mock(AIServiceInterface::class);
        $this->app->instance(AIServiceInterface::class, $this->mockAIService);
    }

    /**
     * Test paginated notes retrieval.
     */
    public function test_can_get_paginated_notes(): void
    {
        // Seed some notes
        Note::factory()->count(15)->create();

        $response = $this->getJson('/api/notes?limit=10');

        $response->assertStatus(200)
             ->assertJsonStructure([
                 'success',
                 'message',
                 'data' => [
                     '*' => ['id', 'title', 'content', 'created_at', 'updated_at']
                 ],
                 'meta' => ['current_page', 'last_page', 'per_page', 'total']
             ]);

        $this->assertCount(10, $response->json('data'));
    }

    /**
     * Test note creation with valid inputs.
     */
    public function test_can_create_note_with_embedding(): void
    {
        $mockEmbedding = array_fill(0, 1536, 0.1);
        
        // Assert AIServiceInterface->generateEmbedding is called once
        $this->mockAIService->shouldReceive('generateEmbedding')
            ->once()
            ->andReturn($mockEmbedding);

        $payload = [
            'title' => 'Feature Test Note',
            'content' => 'This is a note for testing OpenAI embedding creation.'
        ];

        $response = $this->postJson('/api/notes', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'title', 'content', 'created_at', 'updated_at']
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Note created successfully',
                'data' => [
                    'title' => 'Feature Test Note',
                    'content' => 'This is a note for testing OpenAI embedding creation.'
                ]
            ]);

        $this->assertDatabaseHas('notes', [
            'title' => 'Feature Test Note',
            'content' => 'This is a note for testing OpenAI embedding creation.'
        ]);
        
        $note = Note::first();
        $this->assertEquals($mockEmbedding, $note->embedding);
    }

    /**
     * Test creation validation failures.
     */
    public function test_create_note_validation_fails(): void
    {
        // Missing title and content
        $response = $this->postJson('/api/notes', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation error'
            ])
            ->assertJsonStructure(['errors' => ['title', 'content']]);
    }

    /**
     * Test retrieving a single note.
     */
    public function test_can_retrieve_single_note(): void
    {
        $note = Note::factory()->create();

        $response = $this->getJson("/api/notes/{$note->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $note->id,
                    'title' => $note->title,
                    'content' => $note->content
                ]
            ]);
    }

    /**
     * Test retrieving non-existent note returns 404.
     */
    public function test_retrieve_non_existent_note_returns_404(): void
    {
        $response = $this->getJson('/api/notes/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Note not found'
            ]);
    }

    /**
     * Test updating a note.
     */
    public function test_can_update_note(): void
    {
        $note = Note::factory()->create();
        $mockEmbedding = array_fill(0, 1536, 0.2);

        $this->mockAIService->shouldReceive('generateEmbedding')
            ->once()
            ->andReturn($mockEmbedding);

        $payload = [
            'title' => 'Updated Feature Test Title',
            'content' => 'Updated content here.'
        ];

        $response = $this->putJson("/api/notes/{$note->id}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Note updated successfully',
                'data' => [
                    'id' => $note->id,
                    'title' => 'Updated Feature Test Title',
                    'content' => 'Updated content here.'
                ]
            ]);

        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'title' => 'Updated Feature Test Title',
            'content' => 'Updated content here.'
        ]);
    }

    /**
     * Test deleting a note.
     */
    public function test_can_delete_note(): void
    {
        $note = Note::factory()->create();

        $response = $this->deleteJson("/api/notes/{$note->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Note deleted successfully'
            ]);

        $this->assertDatabaseMissing('notes', [
            'id' => $note->id
        ]);
    }

    /**
     * Test generating AI note summary.
     */
    public function test_can_generate_summary(): void
    {
        $note = Note::factory()->create(['content' => 'Detailed programming concepts.']);
        $mockSummary = "1. Detailed note content.\n2. Summarized successfully.";

        $this->mockAIService->shouldReceive('generateSummary')
            ->once()
            ->with($note->content)
            ->andReturn($mockSummary);

        $response = $this->postJson("/api/notes/{$note->id}/summary");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Summary generated successfully',
                'data' => [
                    'summary' => $mockSummary
                ]
            ]);
    }

    /**
     * Test semantic search.
     */
    public function test_can_perform_semantic_search(): void
    {
        // Vector size = 1536. Note A has vector high similarity, Note B has orthogonal vector.
        $vecQuery = [1.0, 0.0, 0.0];
        $vecA = [0.95, 0.05, 0.0];
        $vecB = [0.0, 1.0, 0.0];
        
        // Pad vectors to 1536 elements
        $vecQuery = array_pad($vecQuery, 1536, 0.0);
        $vecA = array_pad($vecA, 1536, 0.0);
        $vecB = array_pad($vecB, 1536, 0.0);

        $noteA = Note::factory()->create(['title' => 'Python OOP Guide', 'embedding' => $vecA]);
        $noteB = Note::factory()->create(['title' => 'Cooking Recipes', 'embedding' => $vecB]);

        $this->mockAIService->shouldReceive('generateEmbedding')
            ->once()
            ->with('oop guidelines')
            ->andReturn($vecQuery);

        $response = $this->postJson('/api/notes/search', ['query' => 'oop guidelines']);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'title', 'content', 'similarity_score']
                ]
            ]);

        $data = $response->json('data');
        $this->assertCount(2, $data);

        // Note A should be first because similarity is ~0.95 vs 0.0 for Note B
        $this->assertEquals($noteA->id, $data[0]['id']);
        $this->assertGreaterThan(0.9, $data[0]['similarity_score']);
        
        $this->assertEquals($noteB->id, $data[1]['id']);
        $this->assertEqualsWithDelta(0.0, $data[1]['similarity_score'], 0.0001);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
