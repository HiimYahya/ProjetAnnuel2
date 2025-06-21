<?php
session_start();
// api/admin/annonces/post.php

header('Content-Type: application/json');

include '../../../fonctions/db.php';

if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Accès non autorisé']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Données invalides']);
    exit;
}

// Validation simple
$titre = trim($data['titre'] ?? '');
$description = trim($data['description'] ?? '');
$ville_depart = trim($data['ville_depart'] ?? '');
$ville_arrivee = trim($data['ville_arrivee'] ?? '');
$id_client = filter_var($data['id_client'] ?? null, FILTER_VALIDATE_INT);

if (empty($titre) || empty($description) || empty($ville_depart) || empty($ville_arrivee) || empty($id_client)) {
    http_response_code(400);
    echo json_encode(['error' => 'Veuillez remplir tous les champs obligatoires.']);
    exit;
}

try {
    $conn = getConnexion();
    
    $stmt = $conn->prepare("INSERT INTO annonces 
        (id_client, titre, description, ville_depart, ville_arrivee, taille, prix, 
         date_livraison_souhaitee, date_expiration, date_annonce, statut, segmentation_possible) 
        VALUES (:id_client, :titre, :description, :ville_depart, :ville_arrivee, :taille, :prix, 
         :date_livraison, :date_expiration, NOW(), :statut, :segmentation_possible)");

    $stmt->execute([
        ':id_client' => $id_client,
        ':titre' => $titre,
        ':description' => $description,
        ':ville_depart' => $ville_depart,
        ':ville_arrivee' => $ville_arrivee,
        ':taille' => $data['taille'] ?? 0,
        ':prix' => $data['prix'] ?? 0,
        ':date_livraison' => !empty($data['date_livraison']) ? $data['date_livraison'] : null,
        ':date_expiration' => !empty($data['date_expiration']) ? $data['date_expiration'] : null,
        ':statut' => $data['statut'] ?? 'actif',
        ':segmentation_possible' => isset($data['segmentation_possible']) ? 1 : 0
    ]);

    http_response_code(201); // Created
    echo json_encode(['success' => 'Annonce ajoutée avec succès.']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
} 