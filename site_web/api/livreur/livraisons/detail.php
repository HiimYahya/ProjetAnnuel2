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
<<<<<<< HEAD
// Annonce (tous les champs)
$stmtAnnonce = $conn->prepare("SELECT * FROM annonces WHERE id = ?");
$stmtAnnonce->execute([$id_annonce]);
$annonce = $stmtAnnonce->fetch(PDO::FETCH_ASSOC);
=======
>>>>>>> d17c8ef584a4a876f47e451e8a1a3a9ec69141b3
// Livraison
$stmt = $conn->prepare("SELECT l.*, u.nom AS nom_livreur FROM livraisons l LEFT JOIN utilisateurs u ON l.id_livreur = u.id WHERE l.id_annonce = ?");
$stmt->execute([$id_annonce]);
$livraison = $stmt->fetch(PDO::FETCH_ASSOC);
<<<<<<< HEAD
$pret_a_commencer = false;
$id_first = null;
$id_prev = null;
$statut_prev = null;
$is_last = false;
if ($livraison && $annonce) {
    // Récupérer l'origine de la chaîne
    $id_annonce_origine = $annonce['id_annonce_origine'] ?: $annonce['id'];
    // Est-ce le premier segment ?
    $stmtFirst = $conn->prepare("SELECT l.id FROM livraisons l JOIN annonces a ON l.id_annonce = a.id WHERE (a.id_annonce_origine = ? OR a.id = ?) ORDER BY l.date_prise_en_charge ASC LIMIT 1");
    $stmtFirst->execute([$id_annonce_origine, $id_annonce_origine]);
    $id_first = $stmtFirst->fetchColumn();
    // Chercher le segment précédent (par date_prise_en_charge)
    if ($id_first != $livraison['id']) {
        $stmtPrev = $conn->prepare("SELECT l.id, l.statut FROM livraisons l JOIN annonces a ON l.id_annonce = a.id WHERE (a.id_annonce_origine = ? OR a.id = ?) AND l.date_prise_en_charge < ? ORDER BY l.date_prise_en_charge DESC LIMIT 1");
        $stmtPrev->execute([$id_annonce_origine, $id_annonce_origine, $livraison['date_prise_en_charge']]);
        $rowPrev = $stmtPrev->fetch(PDO::FETCH_ASSOC);
        if ($rowPrev) {
            $id_prev = $rowPrev['id'];
            $statut_prev = $rowPrev['statut'];
        }
    }
    if ($id_first == $livraison['id']) {
        // 1. Compter le nombre d'annonces de la chaîne
        $stmtCountAnnonces = $conn->prepare("SELECT COUNT(*) FROM annonces WHERE id_annonce_origine = ? OR id = ?");
        $stmtCountAnnonces->execute([$id_annonce_origine, $id_annonce_origine]);
        $total_annonces = $stmtCountAnnonces->fetchColumn();
        // 2. Compter le nombre de livraisons prêtes (validées + livreur assigné) pour la chaîne
        $stmtCountLivraisons = $conn->prepare("
            SELECT COUNT(*) FROM livraisons l
            JOIN annonces a ON l.id_annonce = a.id
            WHERE (a.id_annonce_origine = ? OR a.id = ?)
              AND l.validation_client = 1
              AND l.id_livreur IS NOT NULL
        ");
        $stmtCountLivraisons->execute([$id_annonce_origine, $id_annonce_origine]);
        $total_livraisons_pretes = $stmtCountLivraisons->fetchColumn();
        // 3. Le badge n'est affiché que si tous les segments sont couverts et validés
        $pret_a_commencer = ($total_annonces == $total_livraisons_pretes);
    }
    // Déterminer si c'est le dernier segment (plus robuste)
    $stmtAll = $conn->prepare("SELECT l.id, l.statut FROM livraisons l JOIN annonces a ON l.id_annonce = a.id WHERE (a.id_annonce_origine = ? OR a.id = ?) ORDER BY l.date_prise_en_charge ASC, l.id ASC");
    $stmtAll->execute([$id_annonce_origine, $id_annonce_origine]);
    $all_rows = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
    $all_ids = array_column($all_rows, 'id');
    $all_livraisons = [];
    foreach ($all_rows as $row) {
        $all_livraisons[$row['id']] = $row;
    }
    $is_last = (end($all_ids) == $livraison['id']);
}
echo json_encode([
    'annonce' => $annonce,
    'livraison' => $livraison,
    'pret_a_commencer' => $pret_a_commencer,
    'id_first' => $id_first,
    'id_prev' => $id_prev,
    'statut_prev' => $statut_prev,
    'is_last' => $is_last,
    'all_ids' => $all_ids,
    'all_livraisons' => $all_livraisons
]); 
=======
// Annonce
$stmtAnnonce = $conn->prepare("SELECT * FROM annonces WHERE id = ?");
$stmtAnnonce->execute([$id_annonce]);
$annonce = $stmtAnnonce->fetch(PDO::FETCH_ASSOC);
// Segments
$stmt_segments = $conn->prepare("SELECT s.*, u.nom AS nom_livreur, pr_depart.nom AS point_relais_depart_nom, pr_depart.ville AS point_relais_depart_ville, pr_arrivee.nom AS point_relais_arrivee_nom, pr_arrivee.ville AS point_relais_arrivee_ville FROM segments s LEFT JOIN utilisateurs u ON s.id_livreur = u.id LEFT JOIN points_relais pr_depart ON s.point_relais_depart = pr_depart.id LEFT JOIN points_relais pr_arrivee ON s.point_relais_arrivee = pr_arrivee.id WHERE s.id_annonce = ? ORDER BY s.id");
$stmt_segments->execute([$id_annonce]);
$segments = $stmt_segments->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(['livraison' => $livraison, 'annonce' => $annonce, 'segments' => $segments]); 
>>>>>>> d17c8ef584a4a876f47e451e8a1a3a9ec69141b3
