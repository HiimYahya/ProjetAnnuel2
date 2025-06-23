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
$stmt = $conn->prepare("SELECT DISTINCT ville_depart as ville FROM annonces WHERE ville_depart IS NOT NULL UNION SELECT DISTINCT ville_arrivee as ville FROM annonces WHERE ville_arrivee IS NOT NULL ORDER BY ville");
$stmt->execute();
$villes = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo json_encode(['villes' => $villes]); 