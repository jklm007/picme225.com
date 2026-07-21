<?php
// Simple translation test - no Laravel bootstrap needed
$lang = 'fr';
$file = "/app/resources/lang/{$lang}/home.php";
if (file_exists($file)) {
    $arr = include($file);
    echo "[$lang] location = " . ($arr['location'] ?? 'KEY MISSING') . "\n";
    echo "[$lang] drive = " . ($arr['drive'] ?? 'KEY MISSING') . "\n";
    echo "[$lang] total keys: " . count($arr) . "\n";
} else {
    echo "FILE MISSING: $file\n";
}

$lang = 'en';
$file = "/app/resources/lang/{$lang}/home.php";
if (file_exists($file)) {
    $arr = include($file);
    echo "[$lang] location = " . ($arr['location'] ?? 'KEY MISSING') . "\n";
    echo "[$lang] drive = " . ($arr['drive'] ?? 'KEY MISSING') . "\n";
    echo "[$lang] total keys: " . count($arr) . "\n";
} else {
    echo "FILE MISSING: $file\n";
}

// Check PHP parse error
echo "\nSyntax check en/home.php:\n";
$output = shell_exec("php -l /app/resources/lang/en/home.php 2>&1");
echo $output;
echo "\nSyntax check fr/home.php:\n";
$output = shell_exec("php -l /app/resources/lang/fr/home.php 2>&1");
echo $output;
