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
$filtre_ville = $_GET['ville'] ?? '';
$filtre_prix_min = $_GET['prix_min'] ?? '';
$filtre_prix_max = $_GET['prix_max'] ?? '';
$filtre_tri = $_GET['tri'] ?? 'recent';
$where_clauses = ["a.statut = 'en attente'"];
$params = [];
if (!empty($filtre_ville)) {
    $where_clauses[] = "(a.ville_depart LIKE ? OR a.ville_arrivee LIKE ?)";
    $search_term = "%$filtre_ville%";
    $params = array_merge($params, [$search_term, $search_term]);
}
if (!empty($filtre_prix_min) && is_numeric($filtre_prix_min)) {
    $where_clauses[] = "a.prix >= ?";
    $params[] = $filtre_prix_min;
}
if (!empty($filtre_prix_max) && is_numeric($filtre_prix_max)) {
    $where_clauses[] = "a.prix <= ?";
    $params[] = $filtre_prix_max;
}
$where_clause = implode(' AND ', $where_clauses);
$order_by = match($filtre_tri) {
    'prix_asc' => 'a.prix ASC',
    'prix_desc' => 'a.prix DESC',
    'date_livraison' => 'a.date_livraison_souhaitee ASC',
    default => 'a.date_annonce DESC'
};
$sql = "SELECT a.*, u.nom AS nom_client FROM annonces a JOIN utilisateurs u ON a.id_client = u.id WHERE $where_clause ORDER BY $order_by";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$annonces = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(['annonces' => $annonces]); 