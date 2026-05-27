<?php

namespace App\Services;

use App\Repositories\NoteRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Exception;

class SummaryService
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
     * Get or generate a summary for a note.
     * Caches summary in Redis to minimize API costs.
     *
     * @param int $noteId
     * @return string
     * @throws Exception
     */
    public function getNoteSummary(int $noteId): string
    {
        $note = $this->noteRepository.findNoteById($noteId);

        if (!$note) {
            throw new Exception('Note not found', 404);
        }

        $cacheKey = "note_summary_{$noteId}";

        // Retrieve from cache if exists
        $cachedSummary = Cache::get($cacheKey);
        if ($cachedSummary) {
            return $cachedSummary;
        }

        // Generate summary via OpenAI
        $summary = $this->openAIService->generateSummary($note->content);

        // Store in cache for 24 hours
        Cache::put($cacheKey, $summary, now()->addDay());

        return $summary;
    }

    /**
     * Invalidate the cached summary for a specific note.
     * Call this when a note is updated or deleted.
     *
     * @param int $noteId
     * @return void
     */
    public function invalidateSummaryCache(int $noteId): void
    {
        Cache::forget("note_summary_{$noteId}");
    }
}
