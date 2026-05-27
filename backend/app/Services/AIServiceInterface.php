<?php

namespace App\Services;

interface AIServiceInterface
{
    /**
     * Generate embedding vector for a given text.
     *
     * @param string $text
     * @return array
     */
    public function generateEmbedding(string $text): array;

    /**
     * Generate note summary.
     *
     * @param string $content
     * @return string
     */
    public function generateSummary(string $content): string;
}
