<?php
header('Content-Type: text/plain');
$lines = file(__DIR__.'/../app/Services/SummaryService.php');
foreach ($lines as $i => $line) {
    echo ($i+1) . ": " . $line;
}
