<?php
header('Content-Type: text/plain');
$content = file_get_contents(__DIR__.'/../app/Services/SummaryService.php');
$tokens = token_get_all($content);
foreach ($tokens as $token) {
    if (is_array($token)) {
        $name = token_name($token[0]);
        $text = trim($token[1]);
        if ($token[2] >= 28 && $token[2] <= 36) {
            echo "Line " . $token[2] . " | " . $name . ": '" . $text . "'\n";
        }
    } else {
        echo "SIMPLE: '" . $token . "'\n";
    }
}
