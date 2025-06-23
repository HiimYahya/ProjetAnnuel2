<?php
session_start();
// api/admin/annonces/put.php

header('Content-Type: application/json');

include '../../../fonctions/db.php';

if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Accès non autorisé']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID d\'annonce manquant ou invalide.']);
    exit;
}

// Validation
$titre = trim($data['titre'] ?? '');
if (empty($titre)) {
    http_response_code(400);
    echo json_encode(['error' => 'Le titre est obligatoire.']);
    exit;
}

try {
    $conn = getConnexion();
    
    $stmt = $conn->prepare("UPDATE annonces SET 
        titre = :titre, 
        description = :description, 
        ville_depart = :ville_depart, 
        ville_arrivee = :ville_arrivee, 
        taille = :taille, 
        prix = :prix, 
        date_livraison_souhaitee = :date_livraison, 
        date_expiration = :date_expiration, 
        statut = :statut, 
        segmentation_possible = :segmentation_possible,
        id_client = :id_client
        WHERE id = :id");

    $stmt->execute([
        ':id' => $id,
        ':titre' => $titre,
        ':description' => trim($data['description'] ?? ''),
        ':ville_depart' => trim($data['ville_depart'] ?? ''),
        ':ville_arrivee' => trim($data['ville_arrivee'] ?? ''),
        ':taille' => $data['taille'] ?? 0,
        ':prix' => $data['prix'] ?? 0,
        ':date_livraison' => !empty($data['date_livraison_souhaitee']) ? $data['date_livraison_souhaitee'] : null,
        ':date_expiration' => !empty($data['date_expiration']) ? $data['date_expiration'] : null,
        ':statut' => $data['statut'] ?? 'actif',
        ':segmentation_possible' => isset($data['segmentation_possible']) && $data['segmentation_possible'] ? 1 : 0,
        ':id_client' => filter_var($data['id_client'] ?? null, FILTER_VALIDATE_INT)
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => 'Annonce mise à jour avec succès.']);
    } else {
        // Potentiellement, aucune modification n'a été faite, ce qui n'est pas une erreur.
        // On peut renvoyer un succès quand même ou un message spécifique.
        echo json_encode(['success' => 'Annonce mise à jour. (Aucune modification détectée)']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
} 