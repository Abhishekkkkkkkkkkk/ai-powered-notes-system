<?php
header('Content-Type: text/plain');

function searchDirectory($dir, $query) {
    $it = new RecursiveDirectoryIterator($dir);
    foreach (new RecursiveIteratorIterator($it) as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            if (strpos($content, $query) !== false) {
                echo "File: " . $file->getPathname() . "\n";
                $lines = explode("\n", $content);
                foreach ($lines as $i => $line) {
                    if (strpos($line, $query) !== false) {
                        echo "  Line " . ($i + 1) . ": " . trim($line) . "\n";
                    }
                }
                echo "\n";
            }
        }
    }
}

echo "Searching for 'findNoteById' in backend...\n\n";
searchDirectory(__DIR__.'/../app', 'findNoteById');
searchDirectory(__DIR__.'/../routes', 'findNoteById');
searchDirectory(__DIR__.'/../tests', 'findNoteById');
