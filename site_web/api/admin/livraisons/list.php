<?php
session_start();
// api/admin/utilisateurs/list.php

header('Content-Type: application/json');

include '../../../fonctions/db.php';

// Authentification de l'administrateur
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Accès non autorisé']);
    exit;
}

try {
    $conn = getConnexion();
    
    // Récupère les livraisons avec le titre de l'annonce associée pour un affichage plus clair
    $query = "SELECT l.id, a.titre 
              FROM livraisons l 
              LEFT JOIN annonces a ON l.id_annonce = a.id 
              ORDER BY l.id DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $livraisons = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($livraisons);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de base de données: ' . $e->getMessage()]);
} 