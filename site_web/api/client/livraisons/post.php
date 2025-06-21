<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../fonctions/db.php';

if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'client') {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

$conn = getConnexion();
$id_client = $_SESSION['utilisateur']['id'];

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';
$id_livraison = $data['id_livraison'] ?? null;
if ($action === 'valider_livraison' && $id_livraison) {
    $stmt = $conn->prepare("UPDATE livraisons SET validation_client = 1, statut = 'en cours' WHERE id = ? AND id_client = ?");
    $stmt->execute([$id_livraison, $id_client]);
    $stmt = $conn->prepare("UPDATE segments SET statut = 'en cours' WHERE id_livraison = ? AND statut = 'en attente'");
    $stmt->execute([$id_livraison]);
    echo json_encode(['success' => true]);
    exit;
}
if ($action === 'confirmer_reception' && $id_livraison) {
    $stmt = $conn->prepare("UPDATE livraisons SET reception_confirmee = 1 WHERE id = ? AND id_client = ?");
    $stmt->execute([$id_livraison, $id_client]);
    echo json_encode(['success' => true]);
    exit;
}
echo json_encode(['error' => 'Action inconnue']); 