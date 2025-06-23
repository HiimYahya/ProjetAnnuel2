<?php
include 'fonctions/db.php';

// Connexion à la base de données
$conn = getConnexion();

// Vérifier si la colonne reception_confirmee existe dans la table livraisons
echo "Vérification de la colonne reception_confirmee dans la table livraisons...\n";

$hasColumn = false;
$columns = $conn->query("SHOW COLUMNS FROM livraisons")->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $column) {
    if ($column['Field'] === 'reception_confirmee') {
        $hasColumn = true;
        break;
    }
}

// Ajouter la colonne manquante
if (!$hasColumn) {
    echo "Ajout de la colonne reception_confirmee à la table livraisons...\n";
    try {
        $conn->exec("ALTER TABLE livraisons ADD COLUMN reception_confirmee TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = réception confirmée par le client, 0 = non confirmée'");
        echo "✅ Colonne reception_confirmee ajoutée avec succès.\n";
    } catch (PDOException $e) {
        echo "❌ Erreur lors de l'ajout de la colonne reception_confirmee: " . $e->getMessage() . "\n";
    }
} else {
    echo "✅ La colonne reception_confirmee existe déjà.\n";
}

// Affiche la structure finale de la table livraisons
echo "\nStructure finale de la table livraisons:\n";
$columns = $conn->query("SHOW COLUMNS FROM livraisons")->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $column) {
    echo $column['Field'] . " - " . $column['Type'] . " - " . ($column['Null'] === "NO" ? "NOT NULL" : "NULL") . "\n";
}

echo "\n==========================================\n";
echo "SQL à exécuter directement dans phpMyAdmin:\n";
echo "ALTER TABLE livraisons ADD COLUMN reception_confirmee TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = réception confirmée par le client, 0 = non confirmée';\n";
echo "==========================================\n";
?> 