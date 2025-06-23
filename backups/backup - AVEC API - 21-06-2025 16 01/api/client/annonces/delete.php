<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'client') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit;
}
require_once '../../../fonctions/db.php';
$conn = getConnexion();
$id_client = $_SESSION['utilisateur']['id'];
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    echo json_encode(['success' => false, 'error' => 'ID manquant']);
    exit;
}
$stmt = $conn->prepare('DELETE FROM annonces WHERE id = ? AND id_client = ?');
$ok = $stmt->execute([$id, $id_client]);
echo json_encode(['success' => $ok]); 