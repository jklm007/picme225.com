<?php

$text = 'Bonjour, vous avez recu un transfert de 200.00 FCFA du 0709112233.';
$textWithoutSpaces = str_replace(' ', '', $text);

preg_match('/(?:recu|montant|de)(\d+(?:[.,]\d+)?)(?:F|FCFA)/i', $textWithoutSpaces, $amountMatches);
$amount = $amountMatches[1] ?? 'NOT FOUND';

preg_match('/(\+?225\s*|0)?([0157]\d{9})/', $textWithoutSpaces, $matches);
$customerPhone = $matches[2] ?? 'NOT FOUND';

echo "Text: $textWithoutSpaces\n";
echo "Amount: $amount\n";
echo "Phone: $customerPhone\n";

// Fallback logic
if ($amount == 'NOT FOUND') {
    preg_match('/(\d+(?:[.,]\d+)?)(?:F|FCFA)/i', $textWithoutSpaces, $amountMatches);
    $amount = $amountMatches[1] ?? 'NOT FOUND FALLBACK';
    echo "Fallback Amount: $amount\n";
}

?>
