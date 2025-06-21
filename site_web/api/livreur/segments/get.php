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
$stmt = $conn->prepare("SELECT s.*, a.titre, l.id_client, u.nom AS nom_client, pr_depart.nom AS point_relais_depart_nom, pr_depart.ville AS point_relais_depart_ville, pr_arrivee.nom AS point_relais_arrivee_nom, pr_arrivee.ville AS point_relais_arrivee_ville FROM segments s JOIN livraisons l ON s.id_livraison = l.id JOIN annonces a ON l.id_annonce = a.id JOIN utilisateurs u ON l.id_client = u.id LEFT JOIN points_relais pr_depart ON s.point_relais_depart = pr_depart.id LEFT JOIN points_relais pr_arrivee ON s.point_relais_arrivee = pr_arrivee.id WHERE s.id_livreur = ? AND l.validation_client = 1 ORDER BY s.id DESC");
$stmt->execute([$id_livreur]);
$segments = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($segments as &$segment) {
    $segment['lieu_depart'] = $segment['point_relais_depart_nom'] ? $segment['point_relais_depart_nom'] . ' - ' . $segment['point_relais_depart_ville'] : $segment['adresse_depart'];
    $segment['lieu_arrivee'] = $segment['point_relais_arrivee_nom'] ? $segment['point_relais_arrivee_nom'] . ' - ' . $segment['point_relais_arrivee_ville'] : $segment['adresse_arrivee'];
}
echo json_encode(['segments' => $segments]); 