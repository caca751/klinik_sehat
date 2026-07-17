<?php
$pdo = new PDO("mysql:host=localhost;dbname=db_apotek;charset=utf8mb4", 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$tables = $pdo->query("SHOW TABLES")->fetchAll();
echo "Tables: " . count($tables) . "\n";
echo "Apotek: " . $pdo->query("SELECT COUNT(*) FROM apotek")->fetchColumn() . "\n";
echo "Harga_stok: " . $pdo->query("SELECT COUNT(*) FROM harga_stok_apotek")->fetchColumn() . "\n";
echo "Obat: " . $pdo->query("SELECT COUNT(*) FROM obat")->fetchColumn() . "\n";
