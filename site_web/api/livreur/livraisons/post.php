<?php
session_start();
header('Content-Type: application/json');
require_once '../../../fonctions/db.php';

if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'livreur') {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

$conn = getConnexion();
$id_livreur = $_SESSION['utilisateur']['id'];
$data = json_decode(file_get_contents('php://input'), true);
$id_annonce = $data['id_annonce'] ?? null;

if (!$id_annonce) {
    http_response_code(400);
    echo json_encode(['error' => "ID d'annonce manquant"]);
    exit;
}

// Vérifier si l'annonce existe et récupérer tous les champs nécessaires
$annonce_stmt = $conn->prepare("SELECT * FROM annonces WHERE id = ?");
$annonce_stmt->execute([$id_annonce]);
$annonce = $annonce_stmt->fetch(PDO::FETCH_ASSOC);

if (!$annonce) {
    http_response_code(404);
    echo json_encode(['error' => "Annonce introuvable"]);
    exit;
}

try {
    // Générer un code de validation aléatoire (6 chiffres)
    $code_validation = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    // Insérer la livraison en dupliquant toutes les infos de l'annonce
    $stmt = $conn->prepare("INSERT INTO livraisons 
        (id_client, id_livreur, id_annonce, date_prise_en_charge, statut, validation_client, segmentation_possible, ville_depart, ville_arrivee, titre, description, prix, hauteur, longueur, largeur, code_validation)
        VALUES (?, ?, ?, NOW(), 'en attente', 0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $annonce['id_client'],
        $id_livreur,
        $id_annonce,
        $annonce['segmentation_possible'],
        $annonce['ville_depart'],
        $annonce['ville_arrivee'],
        $annonce['titre'],
        $annonce['description'],
        $annonce['prix'],
        $annonce['hauteur'] ?? null,
        $annonce['longueur'] ?? null,
        $annonce['largeur'] ?? null,
        $code_validation
    ]);

    // Mettre à jour le statut de l'annonce à "prise en charge"
    $conn->prepare("UPDATE annonces SET statut = 'prise en charge' WHERE id = ?")->execute([$id_annonce]);

    echo json_encode(['success' => true, 'message' => 'Livraison créée et annonce prise en charge.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
} 