<?php

// Boot Laravel container manually
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// Bootstrap the console kernel to initialize database bindings
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Note;

try {
    $notes = Note::all();
    $output = "DATABASE NOTES DUMP:\n";
    $output .= "Total Notes: " . $notes->count() . "\n";
    $output .= "=================================\n\n";

    foreach ($notes as $note) {
        $hasEmbedding = $note->embedding !== null;
        $vectorCount = $hasEmbedding ? count($note->embedding) : 0;
        
        $output .= "ID: {$note->id}\n";
        $output .= "Title: {$note->title}\n";
        $output .= "Content Snippet: " . substr($note->content, 0, 100) . "...\n";
        $output .= "Has Embedding: " . ($hasEmbedding ? "YES ({$vectorCount} dimensions)" : "NO (null)") . "\n";
        $output .= "---------------------------------\n";
    }

    file_put_contents(__DIR__.'/../storage/db_dump.txt', $output);
    echo "Successfully dumped notes list to storage/db_dump.txt";
} catch (Exception $e) {
    file_put_contents(__DIR__.'/../storage/db_dump.txt', "Error during dump: " . $e->getMessage());
    echo "Error: " . $e->getMessage();
}
