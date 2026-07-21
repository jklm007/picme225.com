<?php 
$logFile = "storage/logs/laravel.log"; 
if (file_exists($logFile)) { 
    $content = file_get_contents($logFile); 
    echo nl2br(htmlspecialchars(substr($content, -5000))); 
} else {
    echo "Log file not found.";
}
?>