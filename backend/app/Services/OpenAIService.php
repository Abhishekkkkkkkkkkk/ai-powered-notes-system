<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use OpenAI;

class OpenAIService implements AIServiceInterface
{
    protected ?OpenAI\Client $client = null;
    protected string $embeddingModel;
    protected string $summaryModel;

    public function __construct()
    {
        $apiKey = config('ai.openai.api_key');
        $this->embeddingModel = config('ai.openai.model_embedding', 'text-embedding-3-small');
        $this->summaryModel = config('ai.openai.model_summary', 'gpt-4o-mini');

        if (!empty($apiKey)) {
            try {
                $this->client = OpenAI::client($apiKey);
            } catch (Exception $e) {
                Log::error('Failed to initialize OpenAI client: ' . $e->getMessage());
            }
        }
    }

    /**
     * Set a custom OpenAI client (useful for unit testing).
     */
    public function setClient(OpenAI\Client $client): void
    {
        $this->client = $client;
    }

    /**
     * Generate embedding vector for a given text.
     *
     * @param string $text
     * @return array
     * @throws Exception
     */
    public function generateEmbedding(string $text): array
    {
        if (!$this->client) {
            Log::warning('OpenAI client is not configured. Returning mock/zero embedding.');
            // Return a 1536-dimension mock array of zeros for testing/development fallback
            return array_fill(0, 1536, 0.0);
        }

        try {
            $response = $this->client->embeddings()->create([
                'model' => $this->embeddingModel,
                'input' => $text,
            ]);

            if (isset($response->embeddings[0]->embedding)) {
                return $response->embeddings[0]->embedding;
            }

            throw new Exception('Invalid response format from OpenAI Embeddings API.');
        } catch (Exception $e) {
            Log::error('OpenAI Embedding error: ' . $e->getMessage());
            Log::warning('OpenAI API error/quota limit. Falling back to mock vector embedding.');
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
        if (!$this->client) {
            Log::warning('OpenAI client is not configured. Returning mock summary.');
            return "This is a placeholder summary. Please configure OPENAI_API_KEY to generate real summaries.";
        }

        try {
            $response = $this->client->chat()->create([
                'model' => $this->summaryModel,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a helpful notes assistant. Summarize the following note clearly in 3-5 concise lines. Keep it simple and relevant.'
                    ],
                    [
                        'role' => 'user',
                        'content' => "Note content:\n\n" . $content
                    ]
                ],
                'temperature' => 0.5,
                'max_tokens' => 150
            ]);

            if (isset($response->choices[0]->message->content)) {
                return trim($response->choices[0]->message->content);
            }

            throw new Exception('Invalid response format from OpenAI Chat API.');
        } catch (Exception $e) {
            Log::error('OpenAI Summarization error: ' . $e->getMessage());
            Log::warning('OpenAI API error/quota limit. Falling back to mock summary.');
            return $this->generateMockSummary($content);
        }
    }

    /**
     * Helper to generate a mathematically normalized mock embedding based on string hash.
     */
    private function generateMockEmbedding(string $text): array
    {
        $hash = md5($text);
        $vector = [];
        $sumSq = 0.0;
        
        for ($i = 0; $i < 1536; $i++) {
            $charIndex = $i % 32;
            $val = hexdec(substr($hash, $charIndex, 1)) / 15.0; // Between 0.0 and 1.0
            $vector[] = $val;
            $sumSq += $val * $val;
        }
        
        $magnitude = sqrt($sumSq);
        if ($magnitude > 0) {
            for ($i = 0; $i < 1536; $i++) {
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
            return "[AI Fallback Notice: Your OpenAI Key has no remaining quota.]\nThis note does not contain enough text sentences to generate an automatic preview summary.";
        }
        
        return "[AI Fallback Summary (OpenAI Quota Exceeded)]:\n- " . implode(".\n- ", $summaryLines) . ".";
    }
}
