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
$stmt = $conn->prepare("SELECT s.id, s.id_annonce, s.adresse_depart, s.adresse_arrivee, s.date_debut, s.date_fin, a.titre, pr.nom AS point_relais_nom, pr.ville AS point_relais_ville FROM segments s JOIN annonces a ON s.id_annonce = a.id LEFT JOIN points_relais pr ON s.point_relais_arrivee = pr.id LEFT JOIN logs log ON log.details LIKE CONCAT('%Segment #', s.id, '%') AND log.action = 'livraison_point_relais' WHERE log.id_utilisateur = ? AND s.statut = 'en point relais' AND s.id_livreur IS NULL ORDER BY s.date_fin DESC LIMIT 10");
try {
    $stmt->execute([$id_livreur]);
    $historique = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($historique as &$h) {
        $h['date_fin'] = $h['date_fin'] ? date('d/m/Y H:i', strtotime($h['date_fin'])) : '';
    }
} catch (Exception $e) {
    $historique = [];
}
echo json_encode(['historique' => $historique]); 