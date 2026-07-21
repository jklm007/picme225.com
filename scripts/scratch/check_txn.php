<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$passbooks = DB::table('wallet_passbooks')->where('transaction_id', 'like', '%16712%')->get();
foreach ($passbooks as $p) {
    echo "ID: {$p->id} | UserID: {$p->user_id} | Amount: {$p->amount} | Txn: {$p->transaction_id} | Date: {$p->created_at}\n";
}
if ($passbooks->isEmpty()) {
    echo "NO PASSBOOK FOUND FOR 16712\n";
}
