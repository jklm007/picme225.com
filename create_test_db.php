<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    $pdo->exec('DROP DATABASE IF EXISTS homestead_test');
    $pdo->exec('CREATE DATABASE homestead_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    echo "Database 'homestead_test' dropped and recreated successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
