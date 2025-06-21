<?php
include 'fonctions/db.php';

// Connexion à la base de données
$conn = getConnexion();

echo "Liste des tables dans la base de données :\n";
$tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    echo "- $table\n";
}
echo "\n";

// Vérifier si la table livraisons existe
$tables = $conn->query("SHOW TABLES LIKE 'livraisons'")->fetchAll();
echo "Table livraisons existe: " . (count($tables) > 0 ? "Oui" : "Non") . "\n";

if (count($tables) > 0) {
    // Afficher la structure de la table livraisons
    $columns = $conn->query("SHOW COLUMNS FROM livraisons")->fetchAll(PDO::FETCH_ASSOC);
    echo "Structure de la table livraisons:\n";
    foreach ($columns as $column) {
        echo $column['Field'] . " - " . $column['Type'] . " - " . ($column['Null'] === "NO" ? "NOT NULL" : "NULL") . "\n";
    }
    
    // Vérifier si validation_client existe
    $hasValidationClient = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'validation_client') {
            $hasValidationClient = true;
            break;
        }
    }
    echo "Colonne validation_client existe: " . ($hasValidationClient ? "Oui" : "Non") . "\n";
}

// Vérifier si la table segments existe
$tables = $conn->query("SHOW TABLES LIKE 'segments'")->fetchAll();
echo "\nTable segments existe: " . (count($tables) > 0 ? "Oui" : "Non") . "\n";

if (count($tables) > 0) {
    // Afficher la structure de la table segments
    $columns = $conn->query("SHOW COLUMNS FROM segments")->fetchAll(PDO::FETCH_ASSOC);
    echo "Structure de la table segments:\n";
    foreach ($columns as $column) {
        echo $column['Field'] . " - " . $column['Type'] . " - " . ($column['Null'] === "NO" ? "NOT NULL" : "NULL") . "\n";
    }
    
    // Vérifier si id_livraison existe
    $hasIdLivraison = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'id_livraison') {
            $hasIdLivraison = true;
            break;
        }
    }
    echo "Colonne id_livraison existe: " . ($hasIdLivraison ? "Oui" : "Non") . "\n";
}

// Vérifier si la table points_relais existe
$tables = $conn->query("SHOW TABLES LIKE 'points_relais'")->fetchAll();
echo "\nTable points_relais existe: " . (count($tables) > 0 ? "Oui" : "Non") . "\n";

if (count($tables) > 0) {
    // Compter les points relais
    $count = $conn->query("SELECT COUNT(*) FROM points_relais")->fetchColumn();
    echo "Nombre de points relais: $count\n";
} 