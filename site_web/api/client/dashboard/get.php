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
$annonces = $conn->prepare('SELECT COUNT(*) FROM annonces WHERE id_client = ?');
$annonces->execute([$id_client]);
$livraisons_en_cours = $conn->prepare('SELECT COUNT(*) FROM livraisons WHERE id_client = ? AND statut = "en cours"');
$livraisons_en_cours->execute([$id_client]);
$livraisons_livrees = $conn->prepare('SELECT COUNT(*) FROM livraisons WHERE id_client = ? AND statut = "livrée"');
$livraisons_livrees->execute([$id_client]);
echo json_encode([
  'annonces' => $annonces->fetchColumn(),
  'livraisons_en_cours' => $livraisons_en_cours->fetchColumn(),
  'livraisons_livrees' => $livraisons_livrees->fetchColumn()
]);