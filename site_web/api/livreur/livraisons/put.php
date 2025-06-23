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
$data = json_decode(file_get_contents('php://input'), true);
$id_livraison = $data['id_livraison'] ?? null;

if (!$id_livraison) {
    http_response_code(400);
    echo json_encode(['error' => "ID de livraison manquant"]);
    exit;
}

// Vérifier que la livraison appartient bien à ce livreur
$stmt = $conn->prepare("SELECT * FROM livraisons WHERE id = ? AND id_livreur = ?");
$stmt->execute([$id_livraison, $_SESSION['utilisateur']['id']]);
$livraison = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$livraison) {
    http_response_code(403);
    echo json_encode(['error' => "Accès refusé"]);
    exit;
}

// Marquer comme livrée
$stmt = $conn->prepare("UPDATE livraisons SET statut = 'livrée', date_livraison = NOW() WHERE id = ?");
$stmt->execute([$id_livraison]);

// Trouver et activer le segment suivant dans la chaîne
$stmt = $conn->prepare("SELECT a.id_annonce_origine FROM annonces a WHERE a.id = ?");
$stmt->execute([$livraison['id_annonce']]);
$id_annonce_origine = $stmt->fetchColumn() ?: $livraison['id_annonce'];
// Chercher le segment suivant par date_prise_en_charge
$stmt = $conn->prepare(
    "SELECT l1.id FROM livraisons l1
     JOIN annonces a1 ON l1.id_annonce = a1.id
     WHERE (a1.id_annonce_origine = ? OR a1.id = ?)
     AND l1.date_prise_en_charge > (
         SELECT l2.date_prise_en_charge FROM livraisons l2 WHERE l2.id = ?
     )
     ORDER BY l1.date_prise_en_charge ASC LIMIT 1"
);
$stmt->execute([$id_annonce_origine, $id_annonce_origine, $id_livraison]);
$id_next = $stmt->fetchColumn();
if ($id_next) {
    $stmt = $conn->prepare("SELECT statut FROM livraisons WHERE id = ?");
    $stmt->execute([$id_next]);
    $statut_next = $stmt->fetchColumn();
    if ($statut_next === 'en attente') {
        $conn->prepare("UPDATE livraisons SET statut = 'en cours' WHERE id = ?")->execute([$id_next]);
    }
}

echo json_encode(['success' => true]); 