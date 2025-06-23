<?php
session_start();
if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
    $data = json_decode(file_get_contents('php://input'), true);
    $_POST = $data ?: [];
}
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'livreur') {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

$id_livreur = $_SESSION['utilisateur']['id'];
$id_annonce = $_POST['id_annonce'] ?? null;
$id_point_relais = $_POST['point_relais_arrivee'] ?? null;

if (!$id_annonce || !$id_point_relais) {
    http_response_code(400);
    echo json_encode(['error' => 'Paramètres manquants']);
    exit;
}

// 1. Récupérer l'annonce via la BDD (lecture seule)
require_once '../../../../fonctions/db.php';
$conn = getConnexion();
$stmt = $conn->prepare("SELECT * FROM annonces WHERE id = ?");
$stmt->execute([$id_annonce]);
$annonce = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$annonce) {
    http_response_code(404);
    echo json_encode(['error' => 'Annonce introuvable']);
    exit;
}
// 2. Récupérer l'adresse complète du point relais
$stmt = $conn->prepare("SELECT CONCAT(adresse, ', ', code_postal, ' ', ville) as adresse FROM points_relais WHERE id = ?");
$stmt->execute([$id_point_relais]);
$pr = $stmt->fetch(PDO::FETCH_ASSOC);
$adresse_relais = $pr ? $pr['adresse'] : null;
if (!$adresse_relais) {
    http_response_code(404);
    echo json_encode(['error' => 'Point relais introuvable']);
    exit;
}

try {
    $conn->beginTransaction();
    // Générer un code de validation uniquement si c'est la dernière étape
    $is_last_step = ($adresse_relais === $annonce['ville_arrivee']);
    $code_validation = $is_last_step ? str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT) : null;
    $stmt = $conn->prepare("INSERT INTO livraisons 
        (id_client, id_livreur, id_annonce, date_prise_en_charge, statut, validation_client, segmentation_possible, ville_depart, ville_arrivee, titre, description, prix, hauteur, longueur, largeur, code_validation)
        VALUES (?, ?, ?, NOW(), 'en attente', 0, 1, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $annonce['id_client'],
        $id_livreur,
        $id_annonce,
        $annonce['ville_depart'],
        $adresse_relais,
        $annonce['titre'],
        $annonce['description'],
        $annonce['prix'],
        $annonce['hauteur'] ?? null,
        $annonce['longueur'] ?? null,
        $annonce['largeur'] ?? null,
        $code_validation
    ]);
    $stmt = $conn->prepare("INSERT INTO annonces 
        (id_client, titre, description, ville_depart, ville_arrivee, date_annonce, statut, prix, date_livraison_souhaitee, segmentation_possible, hauteur, longueur, largeur)
        VALUES (?, ?, ?, ?, ?, NOW(), 'en attente', ?, ?, 1, ?, ?, ?)");
    $stmt->execute([
        $annonce['id_client'],
        $annonce['titre'],
        $annonce['description'],
        $adresse_relais,
        $annonce['ville_arrivee'],
        $annonce['prix'],
        $annonce['date_livraison_souhaitee'],
        $annonce['hauteur'] ?? null,
        $annonce['longueur'] ?? null,
        $annonce['largeur'] ?? null
    ]);
    $conn->prepare("UPDATE annonces SET statut = 'prise en charge' WHERE id = ?")->execute([$id_annonce]);
    $conn->commit();
    echo json_encode(['success' => true]);
    exit;
} catch (Exception $e) {
    $conn->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Erreur creation segment : ' . $e->getMessage()]);
    exit;
} 