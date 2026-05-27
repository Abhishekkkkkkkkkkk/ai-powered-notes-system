<?php

namespace App\Services;

use App\Repositories\NoteRepositoryInterface;
use Exception;

class SemanticSearchService
{
    protected NoteRepositoryInterface $noteRepository;
    protected AIServiceInterface $openAIService;

    public function __construct(
        NoteRepositoryInterface $noteRepository,
        AIServiceInterface $openAIService
    ) {
        $this->noteRepository = $noteRepository;
        $this->openAIService = $openAIService;
    }

    /**
     * Search notes semantically using OpenAI vector embeddings and cosine similarity.
     *
     * @param string $queryText
     * @param int $limit
     * @return array
     * @throws Exception
     */
    public function search(string $queryText, int $limit = 10): array
    {
        if (empty(trim($queryText))) {
            return [];
        }

        // 1. Generate embedding for query text
        $queryEmbedding = $this->openAIService->generateEmbedding($queryText);

        // 2. Fetch all notes with embeddings
        $notes = $this->noteRepository->getAllNotesWithEmbeddings();

        $results = [];

        // 3. Compare query embedding with stored embeddings
        foreach ($notes as $note) {
            $noteEmbedding = $note->embedding;

            if (is_array($noteEmbedding) && count($noteEmbedding) > 0) {
                $score = $this->cosineSimilarity($queryEmbedding, $noteEmbedding);
                
                // Add similarity score to note attribute
                $note->similarity_score = $score;
                
                $results[] = $note;
            }
        }

        // 4. Sort by highest similarity
        usort($results, function ($a, $b) {
            return $b->similarity_score <=> $a->similarity_score;
        });

        // 5. Slice top results
        return array_slice($results, 0, $limit);
    }

    /**
     * Calculate cosine similarity between two vector arrays.
     *
     * Formula: (A . B) / (||A|| * ||B||)
     *
     * @param array $vec1
     * @param array $vec2
     * @return float
     */
    public function cosineSimilarity(array $vec1, array $vec2): float
    {
        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        $count = count($vec1);
        $count2 = count($vec2);

        // Safety check
        if ($count === 0 || $count2 === 0) {
            return 0.0;
        }

        // Calculate dot product and norms
        for ($i = 0; $i < $count; $i++) {
            $v1 = (float)($vec1[$i] ?? 0.0);
            $v2 = (float)($vec2[$i] ?? 0.0);

            $dotProduct += $v1 * $v2;
            $normA += $v1 * $v1;
        }

        for ($i = 0; $i < $count2; $i++) {
            $v2 = (float)($vec2[$i] ?? 0.0);
            $normB += $v2 * $v2;
        }

        if ($normA === 0.0 || $normB === 0.0) {
            return 0.0;
        }

        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }
}
