<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$text = 'Bonjour, vous avez recu un transfert de 360.00 FCFA du 0709152973. Reference PP260529.1509.D16712. Nouveau solde 4923.50 FCFA.';
$textWithoutSpaces = str_replace(' ', '', $text);
preg_match('/(?:IDTransaction|TransID|Reference|Référence|Ref|Réf|Txn|TransactionID|IDdeTransaction)[:\-]*([A-Z0-9.]+)/i', $textWithoutSpaces, $txnMatches);
$transactionId = !empty($txnMatches[1]) ? strtoupper($txnMatches[1]) : 'TX-' . time();
echo "Txn step 1: " . $transactionId . "\n";

preg_match('/(?:Transaction|ID|Ref|Reference)[:\s]*([A-Z0-9]+)/i', $text, $idMatches);
if (!empty($idMatches[1])) {
    $transactionId = $idMatches[1];
}
if (empty($transactionId)) {
    $transactionId = 'TX-' . time();
}
echo "Txn step 2: " . $transactionId . "\n";

$exists = DB::table('wallet_passbooks')->where('transaction_id', $transactionId)->whereNotNull('transaction_id')->exists();
echo "Exists in DB for '$transactionId': " . ($exists ? 'YES' : 'NO') . "\n";
