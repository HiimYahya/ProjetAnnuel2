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
$stmt = $conn->prepare("SELECT l.*, u.nom AS nom_client FROM livraisons l JOIN utilisateurs u ON l.id_client = u.id WHERE l.id_livreur = ? ORDER BY l.date_prise_en_charge DESC");
$stmt->execute([$id_livreur]);
$livraisons = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($livraisons as &$livraison) {
    $livraison['date_prise_en_charge'] = $livraison['date_prise_en_charge'] ? date('d/m/Y', strtotime($livraison['date_prise_en_charge'])) : '-';
    $livraison['validation_client'] = (int)$livraison['validation_client'];
    // Détermination de la livrabilité
    $livraison['livrable'] = false;
    // Récupérer l'annonce liée
    $stmtAnnonce = $conn->prepare("SELECT * FROM annonces WHERE id = ?");
    $stmtAnnonce->execute([$livraison['id_annonce']]);
    $annonce = $stmtAnnonce->fetch(PDO::FETCH_ASSOC);
    if ($annonce) {
        $id_annonce_origine = $annonce['id_annonce_origine'] ?: $annonce['id'];
        // Récupérer toutes les livraisons de la chaîne, triées
        $stmtSegs = $conn->prepare("SELECT l.id, l.date_prise_en_charge, l.statut FROM livraisons l JOIN annonces a ON l.id_annonce = a.id WHERE (a.id_annonce_origine = ? OR a.id = ?) ORDER BY l.date_prise_en_charge ASC");
        $stmtSegs->execute([$id_annonce_origine, $id_annonce_origine]);
        $segs = $stmtSegs->fetchAll(PDO::FETCH_ASSOC);
        $idx = array_search($livraison['id'], array_column($segs, 'id'));
        if ($idx === 0) {
            $livraison['livrable'] = true; // Premier segment
        } elseif ($idx > 0) {
            $allPrevDone = true;
            for ($i = 0; $i < $idx; $i++) {
                if ($segs[$i]['statut'] !== 'livrée' && $segs[$i]['statut'] !== 'livré') {
                    $allPrevDone = false;
                    break;
                }
            }
            $livraison['livrable'] = $allPrevDone;
        }
    }
}
// Grouper les livraisons par chaîne (id_annonce_origine ou id)
$groupes = [];
foreach ($livraisons as $k => &$livraison) {
    $stmtAnnonce = $conn->prepare("SELECT * FROM annonces WHERE id = ?");
    $stmtAnnonce->execute([$livraison['id_annonce']]);
    $annonce = $stmtAnnonce->fetch(PDO::FETCH_ASSOC);
    if ($annonce) {
        $id_annonce_origine = $annonce['id_annonce_origine'] ?: $annonce['id'];
        $groupes[$id_annonce_origine][] = &$livraison;
    }
}
// Pour chaque groupe, ne rendre livrable que la plus ancienne 'en cours'
foreach ($groupes as $groupe) {
    // Trier par date_prise_en_charge ASC, puis par id ASC pour robustesse
    usort($groupe, function($a, $b) {
        $cmp = strtotime($a['date_prise_en_charge']) <=> strtotime($b['date_prise_en_charge']);
        if ($cmp === 0) {
            return $a['id'] <=> $b['id'];
        }
        return $cmp;
    });
    $found = false;
    foreach ($groupe as &$livraison) {
        if (!$found && $livraison['statut'] === 'en cours') {
            $livraison['livrable'] = true;
            $found = true;
        } else {
            $livraison['livrable'] = false;
        }
    }
}
echo json_encode(['livraisons' => $livraisons]); 