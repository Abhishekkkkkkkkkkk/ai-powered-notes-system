<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService implements AIServiceInterface
{
    protected string $apiKey;
    protected string $embeddingModel;
    protected string $summaryModel;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('ai.gemini.api_key', '');
        $this->embeddingModel = config('ai.gemini.model_embedding', 'gemini-embedding-001');
        $this->summaryModel = config('ai.gemini.model_summary', 'gemini-1.5-flash');
        $this->baseUrl = 'https://generativelanguage.googleapis.com/v1';
    }

    /**
     * Generate embedding vector for a given text.
     *
     * @param string $text
     * @return array
     */
    public function generateEmbedding(string $text): array
    {
        if (empty($this->apiKey)) {
            Log::warning('Gemini API Key is not configured. Returning mock/zero embedding.');
            return array_fill(0, 768, 0.0);
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/models/{$this->embeddingModel}:embedContent?key={$this->apiKey}", [
                'content' => [
                    'parts' => [
                        ['text' => $text]
                    ]
                ],
                'outputDimensionality' => 768
            ]);

            if ($response->successful()) {
                $values = $response->json('embedding.values');
                if (is_array($values) && count($values) > 0) {
                    return $values;
                }
            }

            $errorMessage = $response->json('error.message') ?? 'Unknown API Error';
            throw new Exception("Gemini API Error: {$errorMessage} (Status: {$response->status()})");

        } catch (Exception $e) {
            Log::error('Gemini Embedding error: ' . $e->getMessage());
            Log::warning('Gemini API error/limit. Falling back to mock 768-dimension vector embedding.');
            return $this->generateMockEmbedding($text);
        }
    }

    /**
     * Generate note summary.
     *
     * @param string $content
     * @return string
     */
    public function generateSummary(string $content): string
    {
        if (empty($this->apiKey)) {
            Log::warning('Gemini API Key is not configured. Returning mock summary.');
            return "This is a placeholder summary. Please configure GEMINI_API_KEY to generate real summaries.";
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/models/{$this->summaryModel}:generateContent?key={$this->apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => "You are a helpful notes assistant. Summarize the following note clearly in 3-5 concise lines. Keep it simple and relevant.\n\nNote content:\n\n" . $content
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.5,
                    'maxOutputTokens' => 150
                ]
            ]);

            if ($response->successful()) {
                $text = $response->json('candidates.0.content.parts.0.text');
                if (!empty($text)) {
                    return trim($text);
                }
            }

            $errorMessage = $response->json('error.message') ?? 'Unknown API Error';
            throw new Exception("Gemini API Error: {$errorMessage} (Status: {$response->status()})");

        } catch (Exception $e) {
            Log::error('Gemini Summarization error: ' . $e->getMessage());
            Log::warning('Gemini API error/limit. Falling back to mock summary.');
            return $this->generateMockSummary($content);
        }
    }

    /**
     * Helper to generate a mathematically normalized mock embedding based on string hash.
     * Dimensions are 768 for Gemini text-embedding-004.
     */
    private function generateMockEmbedding(string $text): array
    {
        $hash = md5($text);
        $vector = [];
        $sumSq = 0.0;
        
        for ($i = 0; $i < 768; $i++) {
            $charIndex = $i % 32;
            $val = hexdec(substr($hash, $charIndex, 1)) / 15.0; // Between 0.0 and 1.0
            $vector[] = $val;
            $sumSq += $val * $val;
        }
        
        $magnitude = sqrt($sumSq);
        if ($magnitude > 0) {
            for ($i = 0; $i < 768; $i++) {
                $vector[$i] /= $magnitude;
            }
        }
        
        return $vector;
    }

    /**
     * Helper to extract a mock summary directly from the note text as a fallback.
     */
    private function generateMockSummary(string $content): string
    {
        $sentences = explode('.', strip_tags($content));
        $cleaned = array_filter(array_map('trim', $sentences));
        $summaryLines = array_slice($cleaned, 0, 3);
        
        if (empty($summaryLines)) {
            return "[Gemini Fallback Notice: API Key offline.]\nThis note does not contain enough text sentences to generate an automatic preview summary.";
        }
        
        return "[Gemini Fallback Summary (API Offline)]:\n- " . implode(".\n- ", $summaryLines) . ".";
    }
}
