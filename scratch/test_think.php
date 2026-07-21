<?php
$text = '<think>The user wants me to extract data...</think>{"annonces":[{"is_commercial":true,"title":"Test"}]}';
echo "ORIGINAL LEN: " . strlen($text) . "\n";
$cleaned = preg_replace('/<think>.*?<\/think>/is', '', $text);
echo "CLEANED: " . $cleaned . "\n";
echo "CLEANED LEN: " . strlen($cleaned) . "\n";
$json = json_decode(trim($cleaned), true);
echo "JSON OK: " . ($json !== null ? "YES" : "NO - " . json_last_error_msg()) . "\n";
