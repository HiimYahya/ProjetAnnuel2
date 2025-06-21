<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'livreur') {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}
require_once '../../../fonctions/db.php';
$conn = getConnexion();
$stmt = $conn->prepare("SELECT id, nom, ville FROM points_relais ORDER BY ville");
$stmt->execute();
$points_relais = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(['points_relais' => $points_relais]); 