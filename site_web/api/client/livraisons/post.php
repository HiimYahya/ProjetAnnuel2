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

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';
$id_livraison = $data['id_livraison'] ?? null;
if ($action === 'valider_livraison' && $id_livraison) {
<<<<<<< HEAD
    // 1. Valider la livraison
    $stmt = $conn->prepare("UPDATE livraisons SET validation_client = 1 WHERE id = ? AND id_client = ?");
    $stmt->execute([$id_livraison, $id_client]);

    // 2. Récupérer l'annonce liée à cette livraison
    $stmt = $conn->prepare("SELECT a.id_annonce_origine, l.id_annonce FROM livraisons l JOIN annonces a ON l.id_annonce = a.id WHERE l.id = ?");
    $stmt->execute([$id_livraison]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $id_annonce_origine = $row['id_annonce_origine'] ?: $row['id_annonce'];

        // 3. Vérifier si toutes les livraisons de la chaîne sont validées
        $stmt = $conn->prepare(
            "SELECT COUNT(*) FROM livraisons l
             JOIN annonces a ON l.id_annonce = a.id
             WHERE (a.id_annonce_origine = ? OR a.id = ?) AND l.validation_client = 0"
        );
        $stmt->execute([$id_annonce_origine, $id_annonce_origine]);
        $nb_non_validees = $stmt->fetchColumn();

        // 4. Si toutes validées, passer le 1er segment en 'en cours'
        if ($nb_non_validees == 0) {
            // Trouver la première livraison de la chaîne (départ d'origine)
            $stmt = $conn->prepare(
                "SELECT l.id FROM livraisons l
                 JOIN annonces a ON l.id_annonce = a.id
                 WHERE (a.id_annonce_origine = ? OR a.id = ?)
                 ORDER BY l.date_prise_en_charge ASC LIMIT 1"
            );
            $stmt->execute([$id_annonce_origine, $id_annonce_origine]);
            $id_livraison_first = $stmt->fetchColumn();
            if ($id_livraison_first) {
                $conn->prepare("UPDATE livraisons SET statut = 'en cours' WHERE id = ?")->execute([$id_livraison_first]);
            }
        } else {
            // Trouver le segment suivant dans la chaîne
            $stmt = $conn->prepare(
                "SELECT l1.id, l1.date_prise_en_charge FROM livraisons l1
                 JOIN annonces a1 ON l1.id_annonce = a1.id
                 WHERE (a1.id_annonce_origine = ? OR a1.id = ?)
                 ORDER BY l1.date_prise_en_charge ASC"
            );
            $stmt->execute([$id_annonce_origine, $id_annonce_origine]);
            $segments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $idx = array_search($id_livraison, array_column($segments, 'id'));
            if ($idx !== false && isset($segments[$idx+1])) {
                // Vérifier que le segment suivant n'est pas déjà en cours ou livré
                $id_next = $segments[$idx+1]['id'];
                $stmt = $conn->prepare("SELECT statut FROM livraisons WHERE id = ?");
                $stmt->execute([$id_next]);
                $statut_next = $stmt->fetchColumn();
                if ($statut_next === 'en attente') {
                    $conn->prepare("UPDATE livraisons SET statut = 'en cours' WHERE id = ?")->execute([$id_next]);
                }
            }
        }
    }

=======
    $stmt = $conn->prepare("UPDATE livraisons SET validation_client = 1, statut = 'en cours' WHERE id = ? AND id_client = ?");
    $stmt->execute([$id_livraison, $id_client]);
    $stmt = $conn->prepare("UPDATE segments SET statut = 'en cours' WHERE id_livraison = ? AND statut = 'en attente'");
    $stmt->execute([$id_livraison]);
>>>>>>> d17c8ef584a4a876f47e451e8a1a3a9ec69141b3
    echo json_encode(['success' => true]);
    exit;
}
if ($action === 'confirmer_reception' && $id_livraison) {
    $stmt = $conn->prepare("UPDATE livraisons SET reception_confirmee = 1 WHERE id = ? AND id_client = ?");
    $stmt->execute([$id_livraison, $id_client]);
    echo json_encode(['success' => true]);
    exit;
}
echo json_encode(['error' => 'Action inconnue']); 