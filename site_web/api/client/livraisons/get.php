<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../fonctions/db.php';

if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'client') {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

$conn = getConnexion();
$id_client = $_SESSION['utilisateur']['id'];
$id_annonce = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$id_annonce) {
    echo json_encode(['error' => 'ID manquant']);
    exit;
}

// Récupérer l'annonce
$stmt = $conn->prepare('SELECT * FROM annonces WHERE id = ? AND id_client = ?');
$stmt->execute([$id_annonce, $id_client]);
$annonce = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$annonce) {
    echo json_encode(['error' => 'Annonce introuvable']);
    exit;
}

// Récupérer la livraison associée à cette annonce (si elle existe)
$stmtLiv = $conn->prepare('SELECT l.*, u.nom AS nom_livreur FROM livraisons l LEFT JOIN utilisateurs u ON l.id_livreur = u.id WHERE l.id_annonce = ?');
$stmtLiv->execute([$id_annonce]);
$livraison = $stmtLiv->fetch(PDO::FETCH_ASSOC);

// Récupérer les segments de cette annonce
$stmt2 = $conn->prepare('SELECT s.*, u.nom AS nom_livreur,
    pr_depart.nom AS point_relais_depart_nom, pr_depart.ville AS point_relais_depart_ville,
    pr_arrivee.nom AS point_relais_arrivee_nom, pr_arrivee.ville AS point_relais_arrivee_ville
    FROM segments s
    LEFT JOIN utilisateurs u ON s.id_livreur = u.id
    LEFT JOIN points_relais pr_depart ON s.point_relais_depart = pr_depart.id
    LEFT JOIN points_relais pr_arrivee ON s.point_relais_arrivee = pr_arrivee.id
    WHERE s.id_annonce = ?
    ORDER BY s.id ASC');
$stmt2->execute([$id_annonce]);
$segments = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Réponse structurée
echo json_encode([
    'annonce' => $annonce,
    'livraison' => $livraison,
    'segments' => $segments
]); 