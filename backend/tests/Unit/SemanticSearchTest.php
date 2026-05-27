<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\SemanticSearchService;
use App\Repositories\NoteRepositoryInterface;
use App\Services\AIServiceInterface;
use Mockery;

class SemanticSearchTest extends TestCase
{
    /**
     * Test that two identical vectors return 1.0 similarity.
     */
    public function test_cosine_similarity_perfect_match(): void
    {
        $mockRepo = Mockery::mock(NoteRepositoryInterface::class);
        $mockAI = Mockery::mock(AIServiceInterface::class);
        $service = new SemanticSearchService($mockRepo, $mockAI);

        $v1 = [1.0, 2.0, 3.0];
        $v2 = [1.0, 2.0, 3.0];

        $score = $service->cosineSimilarity($v1, $v2);
        $this->assertEqualsWithDelta(1.0, $score, 0.0001);
    }

    /**
     * Test that two orthogonal vectors return 0.0 similarity.
     */
    public function test_cosine_similarity_orthogonal_vectors(): void
    {
        $mockRepo = Mockery::mock(NoteRepositoryInterface::class);
        $mockAI = Mockery::mock(AIServiceInterface::class);
        $service = new SemanticSearchService($mockRepo, $mockAI);

        $v1 = [1.0, 0.0, 0.0];
        $v2 = [0.0, 1.0, 0.0];

        $score = $service->cosineSimilarity($v1, $v2);
        $this->assertEqualsWithDelta(0.0, $score, 0.0001);
    }

    /**
     * Test that two opposing vectors return -1.0 similarity.
     */
    public function test_cosine_similarity_opposite_vectors(): void
    {
        $mockRepo = Mockery::mock(NoteRepositoryInterface::class);
        $mockAI = Mockery::mock(AIServiceInterface::class);
        $service = new SemanticSearchService($mockRepo, $mockAI);

        $v1 = [1.0, 0.0];
        $v2 = [-1.0, 0.0];

        $score = $service->cosineSimilarity($v1, $v2);
        $this->assertEqualsWithDelta(-1.0, $score, 0.0001);
    }

    /**
     * Test calculation safety when dealing with zero/empty vectors.
     */
    public function test_cosine_similarity_with_zero_vectors(): void
    {
        $mockRepo = Mockery::mock(NoteRepositoryInterface::class);
        $mockAI = Mockery::mock(AIServiceInterface::class);
        $service = new SemanticSearchService($mockRepo, $mockAI);

        $v1 = [0.0, 0.0, 0.0];
        $v2 = [1.0, 2.0, 3.0];

        $score = $service->cosineSimilarity($v1, $v2);
        $this->assertEquals(0.0, $score);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
