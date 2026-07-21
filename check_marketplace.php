<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=homestead', 'root', '');
    
    $tables = ['marketplace_listings', 'wallet_passbooks', 'mobile_money_transactions'];
    foreach ($tables as $table) {
        echo "=== Indexes for $table ===\n";
        try {
            $q = $pdo->query("SHOW INDEX FROM $table");
            while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
                echo "Column: " . $row['Column_name'] . " | Index Name: " . $row['Key_name'] . " | Non_unique: " . $row['Non_unique'] . "\n";
            }
        } catch(Exception $ex) {
            echo "Error: " . $ex->getMessage() . "\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
