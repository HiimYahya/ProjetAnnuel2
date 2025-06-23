<?php
// Script à exécuter une seule fois pour ajouter le champ code_validation à la table livraisons
require_once __DIR__ . '/fonctions/db.php';
$conn = getConnexion();
$sql = "ALTER TABLE livraisons ADD COLUMN code_validation VARCHAR(10) DEFAULT NULL";
try {
    $conn->exec($sql);
    echo "Colonne code_validation ajoutée avec succès.";
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
} 