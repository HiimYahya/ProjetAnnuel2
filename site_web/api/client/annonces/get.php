<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'client') {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}
require_once __DIR__ . '/../../../fonctions/db.php';
$conn = getConnexion();
$id_client = $_SESSION['utilisateur']['id'];
$stmt = $conn->prepare('SELECT * FROM annonces WHERE id_client = ? ORDER BY date_annonce DESC');
$stmt->execute([$id_client]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)); 