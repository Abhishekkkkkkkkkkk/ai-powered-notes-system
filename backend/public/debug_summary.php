<?php
header('Content-Type: text/plain');
echo "SummaryService.php Content in Container:\n\n";
echo file_get_contents(__DIR__.'/../app/Services/SummaryService.php');
