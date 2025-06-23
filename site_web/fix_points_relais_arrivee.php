<?php
include 'fonctions/db.php';

// Connexion à la base de données
$conn = getConnexion();

// Vérifier si les colonnes point_relais_depart et point_relais_arrivee existent dans la table segments
echo "Vérification des colonnes dans la table segments...\n";

$hasPointRelaisDepart = false;
$hasPointRelaisArrivee = false;

$columns = $conn->query("SHOW COLUMNS FROM segments")->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $column) {
    if ($column['Field'] === 'point_relais_depart') {
        $hasPointRelaisDepart = true;
    }
    if ($column['Field'] === 'point_relais_arrivee') {
        $hasPointRelaisArrivee = true;
    }
}

// Ajouter les colonnes manquantes
if (!$hasPointRelaisDepart) {
    echo "Ajout de la colonne point_relais_depart à la table segments...\n";
    try {
        $conn->exec("ALTER TABLE segments ADD COLUMN point_relais_depart int(11) NULL COMMENT 'ID du point relais de départ' AFTER adresse_arrivee");
        echo "✅ Colonne point_relais_depart ajoutée avec succès.\n";
    } catch (PDOException $e) {
        echo "❌ Erreur lors de l'ajout de la colonne point_relais_depart: " . $e->getMessage() . "\n";
    }
} else {
    echo "✅ La colonne point_relais_depart existe déjà.\n";
}

if (!$hasPointRelaisArrivee) {
    echo "Ajout de la colonne point_relais_arrivee à la table segments...\n";
    try {
        $conn->exec("ALTER TABLE segments ADD COLUMN point_relais_arrivee int(11) NULL COMMENT 'ID du point relais d''arrivée' AFTER point_relais_depart");
        echo "✅ Colonne point_relais_arrivee ajoutée avec succès.\n";
    } catch (PDOException $e) {
        echo "❌ Erreur lors de l'ajout de la colonne point_relais_arrivee: " . $e->getMessage() . "\n";
    }
} else {
    echo "✅ La colonne point_relais_arrivee existe déjà.\n";
}

// Récupérer à nouveau les colonnes pour voir si elles ont été correctement ajoutées
$columns = $conn->query("SHOW COLUMNS FROM segments")->fetchAll(PDO::FETCH_ASSOC);
$hasPointRelaisDepart = false;
$hasPointRelaisArrivee = false;
$typeStatut = null;

foreach ($columns as $column) {
    if ($column['Field'] === 'point_relais_depart') {
        $hasPointRelaisDepart = true;
    }
    if ($column['Field'] === 'point_relais_arrivee') {
        $hasPointRelaisArrivee = true;
    }
    if ($column['Field'] === 'statut') {
        $typeStatut = $column['Type'];
    }
}

echo "\nVérification des colonnes après modification:\n";
echo "point_relais_depart: " . ($hasPointRelaisDepart ? "Présent" : "Absent") . "\n";
echo "point_relais_arrivee: " . ($hasPointRelaisArrivee ? "Présent" : "Absent") . "\n";

// Mise à jour du type enum de statut si nécessaire
echo "\nVérification du type enum du champ statut...\n";
if ($typeStatut) {
    echo "Type actuel: " . $typeStatut . "\n";
    
    if (strpos($typeStatut, 'en point relais') === false) {
        echo "Mise à jour du type enum du champ statut...\n";
        try {
            $conn->exec("ALTER TABLE segments MODIFY COLUMN statut ENUM('en attente', 'en cours', 'en point relais', 'livré', 'annulé') NOT NULL DEFAULT 'en attente'");
            echo "✅ Type enum du champ statut mis à jour avec succès.\n";
        } catch (PDOException $e) {
            echo "❌ Erreur lors de la mise à jour du type enum du champ statut: " . $e->getMessage() . "\n";
        }
    } else {
        echo "✅ Le type enum du champ statut est déjà correct.\n";
    }
} else {
    echo "❌ Colonne statut non trouvée.\n";
}

echo "\nScript terminé. Les modifications nécessaires ont été appliquées.\n";

// Générer les commandes SQL à exécuter si les colonnes n'ont pas été ajoutées
if (!$hasPointRelaisDepart || !$hasPointRelaisArrivee) {
    echo "\nCommandes SQL à exécuter manuellement si les colonnes n'ont pas été ajoutées:\n";
    
    if (!$hasPointRelaisDepart) {
        echo "ALTER TABLE segments ADD COLUMN point_relais_depart int(11) NULL COMMENT 'ID du point relais de départ' AFTER adresse_arrivee;\n";
    }
    
    if (!$hasPointRelaisArrivee) {
        echo "ALTER TABLE segments ADD COLUMN point_relais_arrivee int(11) NULL COMMENT 'ID du point relais d''arrivée' AFTER point_relais_depart;\n";
    }
}

// Affiche la structure finale de la table segments
echo "\nStructure finale de la table segments:\n";
$columns = $conn->query("SHOW COLUMNS FROM segments")->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $column) {
    echo $column['Field'] . " - " . $column['Type'] . " - " . ($column['Null'] === "NO" ? "NOT NULL" : "NULL") . "\n";
}
?> 