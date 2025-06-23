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
    // Lister toutes les livraisons du client
    $stmt = $conn->prepare('SELECT l.*, a.titre, a.ville_depart, a.ville_arrivee FROM livraisons l JOIN annonces a ON l.id_annonce = a.id WHERE l.id_client = ? ORDER BY l.date_prise_en_charge DESC');
    $stmt->execute([$id_client]);
    $livraisons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Ajout du champ adresse_arrivee_affichee pour chaque livraison
    foreach ($livraisons as &$liv) {
        $liv['adresse_arrivee_affichee'] = !empty($liv['ville_arrivee']) ? $liv['ville_arrivee'] : '';
    }
    echo json_encode(['livraisons' => $livraisons]);
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
if ($livraison) {
    $livraison['adresse_arrivee_affichee'] = !empty($livraison['ville_arrivee']) ? $livraison['ville_arrivee'] : '';
}

// Réponse structurée
echo json_encode([
    'annonce' => $annonce,
    'livraison' => $livraison
]); 