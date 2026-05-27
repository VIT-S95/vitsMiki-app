<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=default_db;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = file_get_contents(__DIR__ . '/import_data.sql');
    $statements = explode(";\n", $sql);
    
    $count = 0;
    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
    foreach($statements as $stmt) {
        $stmt = trim($stmt);
        if(empty($stmt)) continue;
        $pdo->exec($stmt);
        $count++;
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
    
    echo "Import terminé ! $count lignes insérées.";
} catch(Exception $e) {
    echo "Erreur: " . $e->getMessage();
}
