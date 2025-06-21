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
$id_livreur = $_SESSION['utilisateur']['id'];
$stmt = $conn->prepare("SELECT l.*, a.titre, a.description, a.ville_depart, a.ville_arrivee, u.nom AS nom_client FROM livraisons l JOIN annonces a ON l.id_annonce = a.id JOIN utilisateurs u ON l.id_client = u.id WHERE l.id_livreur = ? ORDER BY l.date_prise_en_charge DESC");
$stmt->execute([$id_livreur]);
$livraisons = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($livraisons as &$livraison) {
    $livraison['date_prise_en_charge'] = $livraison['date_prise_en_charge'] ? date('d/m/Y', strtotime($livraison['date_prise_en_charge'])) : '-';
    $livraison['validation_client'] = (int)$livraison['validation_client'];
}
echo json_encode(['livraisons' => $livraisons]); 