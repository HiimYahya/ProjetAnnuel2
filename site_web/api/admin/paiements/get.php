<?php
session_start();
header('Content-Type: application/json');
include '../../../fonctions/db.php';
include '../../../fonctions/fonctions.php';

if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Accès non autorisé']);
    exit;
}

try {
    $conn = getConnexion();

    // Paramètres de recherche et de pagination
    $search = $_GET['search'] ?? '';
    $statut_filter = $_GET['statut'] ?? '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    // Construction de la clause WHERE
    $where_conditions = [];
    $params = [];

    if (!empty($search)) {
        // La recherche peut se faire sur l'ID, le montant, la méthode...
        // Pour l'exemple, on cherche sur la méthode
        $where_conditions[] = "p.methode LIKE ?";
        $params[] = "%$search%";
    }

    if (!empty($statut_filter)) {
        $where_conditions[] = "p.statut = ?";
        $params[] = $statut_filter;
    }

    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

    // Compter le total pour la pagination
    $stmt_count = $conn->prepare("SELECT COUNT(p.id) FROM paiements p $where_clause");
    $stmt_count->execute($params);
    $total_results = $stmt_count->fetchColumn();
    $total_pages = ceil($total_results / $limit);

    // Récupérer les paiements pour la page courante
    $query = "SELECT p.*, 
                     c.nom as creancier_nom, 
                     d.nom as debiteur_nom
              FROM paiements p
              LEFT JOIN utilisateurs c ON p.id_creancier = c.id
              LEFT JOIN utilisateurs d ON p.id_debiteur = d.id
              $where_clause 
              ORDER BY p.id DESC 
              LIMIT $limit OFFSET $offset";

    $stmt_paiements = $conn->prepare($query);
    $stmt_paiements->execute($params);
    $paiements = $stmt_paiements->fetchAll(PDO::FETCH_ASSOC);

    // Calculer le montant total des paiements effectués (indépendamment des filtres)
    $stmt_total = $conn->query("SELECT SUM(montant) as total FROM paiements WHERE statut = 'effectué'");
    $total_effectue = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    echo json_encode([
        'paiements' => $paiements,
        'currentPage' => $page,
        'totalPages' => $total_pages,
        'totalEffectue' => $total_effectue
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de base de données: ' . $e->getMessage()]);
}
?> 