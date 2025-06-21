<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'client') {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}
require_once '../../../fonctions/db.php';
$conn = getConnexion();
$id_client = $_SESSION['utilisateur']['id'];
$stmt = $conn->prepare('SELECT description, statut FROM prestations WHERE id = ?');
$stmt->execute([$id_client]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)); 