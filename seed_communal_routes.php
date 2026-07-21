<?php
$host = '127.0.0.1';
$db   = 'homestead';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die("Connection failed: " . $e->getMessage());
}

$pdo->beginTransaction();
try {
    // 1. Delete existing communal lines
    $pdo->exec("DELETE FROM pdp_routes WHERE type = 'COMMUNAL'");

    // 2. Insert new lines per commune
    $routes = [
        ['name' => 'Faya - CHU', 'desc' => 'Cocody', 'type' => 'COMMUNAL'],
        ['name' => 'Angré - Riviera 2', 'desc' => 'Cocody', 'type' => 'COMMUNAL'],
        ['name' => 'Palmeraie - 9 Kilos', 'desc' => 'Cocody', 'type' => 'COMMUNAL'],
        ['name' => 'Attoban - Zoo', 'desc' => 'Cocody', 'type' => 'COMMUNAL'],
        ['name' => 'Siporex - Niangon', 'desc' => 'Yopougon', 'type' => 'COMMUNAL'],
        ['name' => 'Maroc - Sideci', 'desc' => 'Yopougon', 'type' => 'COMMUNAL'],
        ['name' => 'Wassakara - Palais', 'desc' => 'Yopougon', 'type' => 'COMMUNAL'],
        ['name' => 'Bel Air - Kouté', 'desc' => 'Yopougon', 'type' => 'COMMUNAL'],
        ['name' => 'Boucle de Marcory', 'desc' => 'Marcory', 'type' => 'COMMUNAL'],
        ['name' => 'Remblais - Champroux', 'desc' => 'Marcory', 'type' => 'COMMUNAL'],
        ['name' => 'Grand Carrefour - Sicogi', 'desc' => 'Koumassi', 'type' => 'COMMUNAL'],
        ['name' => 'Soweto - Remblais', 'desc' => 'Koumassi', 'type' => 'COMMUNAL'],
        ['name' => 'Gare Sud - Carena', 'desc' => 'Plateau', 'type' => 'COMMUNAL'],
        ['name' => 'Indénié - Sorbonne', 'desc' => 'Plateau', 'type' => 'COMMUNAL'],
        ['name' => 'PK 18 - Gare', 'desc' => 'Abobo', 'type' => 'COMMUNAL'],
        ['name' => 'Samaké - Dokui', 'desc' => 'Abobo', 'type' => 'COMMUNAL'],
        ['name' => 'Gare de Bassam - CHU', 'desc' => 'Treichville', 'type' => 'COMMUNAL'],
        ['name' => 'Arras - Belleville', 'desc' => 'Treichville', 'type' => 'COMMUNAL'],
        ['name' => 'Liberté - Renault', 'desc' => 'Adjamé', 'type' => 'COMMUNAL'],
        ['name' => '220 Logements - Forum', 'desc' => 'Adjamé', 'type' => 'COMMUNAL'],
        ['name' => 'Phare - Vridi', 'desc' => 'Port-Bouët', 'type' => 'COMMUNAL'],
        ['name' => 'Aéroport - Gonzagueville', 'desc' => 'Port-Bouët', 'type' => 'COMMUNAL'],
        ['name' => 'Locodjro - Boribana', 'desc' => 'Attécoubé', 'type' => 'COMMUNAL'],
    ];

    $stmt = $pdo->prepare("INSERT INTO pdp_routes (name, description, type, is_active) VALUES (?, ?, ?, 1)");
    foreach ($routes as $route) {
        $stmt->execute([$route['name'], $route['desc'], $route['type']]);
    }
    
    $pdo->commit();
    echo "Seed completed successfully!\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
?>
