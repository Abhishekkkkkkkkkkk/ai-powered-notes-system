<?php

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\SummaryService;

header('Content-Type: text/plain');

try {
    $summaryService = app(SummaryService::class);
    $reflection = new ReflectionClass($summaryService);
    
    echo "SummaryService Properties:\n";
    foreach ($reflection->getProperties() as $prop) {
        $prop->setAccessible(true);
        $val = $prop->getValue($summaryService);
        echo "Property: " . $prop->getName() . "\n";
        echo "  Typehint: " . ($prop->getType() ? $prop->getType()->getName() : 'none') . "\n";
        echo "  Value Class: " . (is_object($val) ? get_class($val) : gettype($val)) . "\n";
    }
    
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
