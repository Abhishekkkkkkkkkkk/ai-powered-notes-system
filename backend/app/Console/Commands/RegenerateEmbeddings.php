<?php

namespace App\Console\Commands;

use App\Models\Note;
use App\Services\AIServiceInterface;
use Illuminate\Console\Command;
use Exception;

class RegenerateEmbeddings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notes:regenerate-embeddings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate AI vector embeddings for all notes in the database using the active provider';

    /**
     * Execute the console command.
     */
    public function handle(AIServiceInterface $aiService): int
    {
        $provider = config('ai.provider', 'openai');
        $this->info("Active AI Provider: " . strtoupper($provider));
        
        $notes = Note::all();
        $count = $notes->count();
        
        if ($count === 0) {
            $this->warn("No notes found in the database.");
            return Command::SUCCESS;
        }

        $this->info("Regenerating embeddings for {$count} notes...");
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $successCount = 0;
        $failCount = 0;

        foreach ($notes as $note) {
            $textToEmbed = "Title: " . $note->title . "\nContent: " . $note->content;
            
            try {
                $embedding = $aiService->generateEmbedding($textToEmbed);
                $note->embedding = $embedding;
                $note->save();
                $successCount++;
            } catch (Exception $e) {
                $this->newLine();
                $this->error("Failed to generate embedding for Note ID {$note->id}: " . $e->getMessage());
                $failCount++;
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Embeddings regeneration completed!");
        $this->info("Successful: {$successCount}");
        if ($failCount > 0) {
            $this->error("Failed: {$failCount}");
        }

        return Command::SUCCESS;
    }
}
