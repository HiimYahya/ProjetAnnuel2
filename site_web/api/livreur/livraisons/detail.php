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
$id_annonce = $_GET['id'] ?? null;
if (!$id_annonce) {
    echo json_encode(['error' => 'ID annonce manquant']);
    exit;
}
// Livraison
$stmt = $conn->prepare("SELECT l.*, u.nom AS nom_livreur FROM livraisons l LEFT JOIN utilisateurs u ON l.id_livreur = u.id WHERE l.id_annonce = ?");
$stmt->execute([$id_annonce]);
$livraison = $stmt->fetch(PDO::FETCH_ASSOC);
// Annonce
$stmtAnnonce = $conn->prepare("SELECT * FROM annonces WHERE id = ?");
$stmtAnnonce->execute([$id_annonce]);
$annonce = $stmtAnnonce->fetch(PDO::FETCH_ASSOC);
// Segments
$stmt_segments = $conn->prepare("SELECT s.*, u.nom AS nom_livreur, pr_depart.nom AS point_relais_depart_nom, pr_depart.ville AS point_relais_depart_ville, pr_arrivee.nom AS point_relais_arrivee_nom, pr_arrivee.ville AS point_relais_arrivee_ville FROM segments s LEFT JOIN utilisateurs u ON s.id_livreur = u.id LEFT JOIN points_relais pr_depart ON s.point_relais_depart = pr_depart.id LEFT JOIN points_relais pr_arrivee ON s.point_relais_arrivee = pr_arrivee.id WHERE s.id_annonce = ? ORDER BY s.id");
$stmt_segments->execute([$id_annonce]);
$segments = $stmt_segments->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(['livraison' => $livraison, 'annonce' => $annonce, 'segments' => $segments]); 