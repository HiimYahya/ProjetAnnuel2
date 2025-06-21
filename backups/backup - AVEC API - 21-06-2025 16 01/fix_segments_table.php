<?php
include 'fonctions/db.php';

// Connexion à la base de données
$conn = getConnexion();

// 1. Ajouter la colonne validation_client à la table livraisons si elle n'existe pas
echo "Vérification de la table livraisons...\n";
$hasValidationClient = false;
$columns = $conn->query("SHOW COLUMNS FROM livraisons")->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $column) {
    if ($column['Field'] === 'validation_client') {
        $hasValidationClient = true;
        break;
    }
}

if (!$hasValidationClient) {
    echo "Ajout de la colonne validation_client à la table livraisons...\n";
    try {
        $conn->exec("ALTER TABLE livraisons ADD COLUMN validation_client TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = validé par le client, 0 = en attente de validation'");
        echo "✅ Colonne validation_client ajoutée avec succès.\n";
    } catch (PDOException $e) {
        echo "❌ Erreur lors de l'ajout de la colonne validation_client: " . $e->getMessage() . "\n";
    }
} else {
    echo "✅ La colonne validation_client existe déjà dans la table livraisons.\n";
}

// 2. Vérifier si la table segments existe, sinon la créer
echo "\nVérification de la table segments...\n";
$tables = $conn->query("SHOW TABLES LIKE 'segments'")->fetchAll();
if (count($tables) == 0) {
    echo "Création de la table segments...\n";
    try {
        $sql = "CREATE TABLE segments (
            id int(11) NOT NULL AUTO_INCREMENT,
            id_livraison int(11) NOT NULL,
            id_annonce int(11) NOT NULL,
            id_livreur int(11) NULL,
            adresse_depart varchar(255) NOT NULL,
            adresse_arrivee varchar(255) NOT NULL,
            point_relais_depart int(11) NULL COMMENT 'ID du point relais de départ',
            point_relais_arrivee int(11) NULL COMMENT 'ID du point relais d\'arrivée',
            statut enum('en attente', 'en cours', 'en point relais', 'livré', 'annulé') NOT NULL DEFAULT 'en attente',
            date_debut timestamp NULL DEFAULT NULL,
            date_fin timestamp NULL DEFAULT NULL,
            PRIMARY KEY (id),
            KEY id_livraison (id_livraison),
            KEY id_annonce (id_annonce),
            KEY id_livreur (id_livreur),
            KEY point_relais_depart (point_relais_depart),
            KEY point_relais_arrivee (point_relais_arrivee)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $conn->exec($sql);
        echo "✅ Table segments créée avec succès.\n";
    } catch (PDOException $e) {
        echo "❌ Erreur lors de la création de la table segments: " . $e->getMessage() . "\n";
    }
} else {
    echo "✅ La table segments existe déjà.\n";
    
    // 3. Vérifier si la colonne id_livraison existe dans la table segments
    $hasIdLivraison = false;
    $columns = $conn->query("SHOW COLUMNS FROM segments")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        if ($column['Field'] === 'id_livraison') {
            $hasIdLivraison = true;
            break;
        }
    }
    
    if (!$hasIdLivraison) {
        echo "Ajout de la colonne id_livraison à la table segments...\n";
        try {
            $conn->exec("ALTER TABLE segments ADD COLUMN id_livraison int(11) NOT NULL AFTER id");
            echo "✅ Colonne id_livraison ajoutée avec succès.\n";
        } catch (PDOException $e) {
            echo "❌ Erreur lors de l'ajout de la colonne id_livraison: " . $e->getMessage() . "\n";
        }
    } else {
        echo "✅ La colonne id_livraison existe déjà dans la table segments.\n";
    }
}

// 4. Vérifier si la table points_relais existe, sinon la créer
echo "\nVérification de la table points_relais...\n";
$tables = $conn->query("SHOW TABLES LIKE 'points_relais'")->fetchAll();
if (count($tables) == 0) {
    echo "Création de la table points_relais...\n";
    try {
        $sql = "CREATE TABLE points_relais (
            id int(11) NOT NULL AUTO_INCREMENT,
            nom varchar(255) NOT NULL,
            adresse varchar(255) NOT NULL,
            code_postal varchar(10) NOT NULL,
            ville varchar(100) NOT NULL,
            coordonnees varchar(50) DEFAULT NULL COMMENT 'Format: latitude,longitude',
            horaires text DEFAULT NULL,
            date_creation timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $conn->exec($sql);
        echo "✅ Table points_relais créée avec succès.\n";
        
        // Ajouter des points relais d'exemple
        echo "Ajout des points relais d'exemple...\n";
        $conn->exec("INSERT INTO points_relais (nom, adresse, code_postal, ville, coordonnees, horaires) 
                    VALUES ('Relay Point Paris', '23 Rue de Rivoli', '75001', 'Paris', '48.8566,2.3522', 'Lun-Ven: 9h-19h, Sam: 10h-18h')");
        $conn->exec("INSERT INTO points_relais (nom, adresse, code_postal, ville, coordonnees, horaires) 
                    VALUES ('Relay Point Lyon', '15 Rue de la République', '69002', 'Lyon', '45.7578,4.8320', 'Lun-Ven: 8h30-19h30, Sam: 9h-18h')");
        $conn->exec("INSERT INTO points_relais (nom, adresse, code_postal, ville, coordonnees, horaires) 
                    VALUES ('Relay Point Marseille', '88 La Canebière', '13001', 'Marseille', '43.2965,5.3698', 'Lun-Ven: 9h-19h, Sam: 9h30-18h30')");
        $conn->exec("INSERT INTO points_relais (nom, adresse, code_postal, ville, coordonnees, horaires) 
                    VALUES ('Relay Point Bordeaux', '45 Rue Sainte-Catherine', '33000', 'Bordeaux', '44.8378,0.5792', 'Lun-Ven: 9h-19h, Sam: 10h-19h')");
        $conn->exec("INSERT INTO points_relais (nom, adresse, code_postal, ville, coordonnees, horaires) 
                    VALUES ('Relay Point Nantes', '12 Rue d\'Orléans', '44000', 'Nantes', '47.2173,1.5534', 'Lun-Ven: 9h-19h, Sam: 9h30-18h30')");
        echo "✅ Points relais d'exemple ajoutés avec succès.\n";
    } catch (PDOException $e) {
        echo "❌ Erreur lors de la création de la table points_relais: " . $e->getMessage() . "\n";
    }
} else {
    echo "✅ La table points_relais existe déjà.\n";
}

echo "\nVérification terminée. Toutes les modifications nécessaires ont été appliquées.\n";
?> 