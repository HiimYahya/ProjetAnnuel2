<?php
session_start();
header('Content-Type: application/json');
include '../../../fonctions/db.php';
include '../../../fonctions/fonctions.php';
include '../../../fonctions/jwt_utils.php';

// Récupération robuste du header Authorization (compatible Apache/Windows)
$authHeader = null;
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
} elseif (function_exists('apache_request_headers')) {
    $headers = apache_request_headers();
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
    }
}

if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
    $jwt = $matches[1];
    $decoded = verify_jwt($jwt);
    if ($decoded && isset($decoded['role']) && $decoded['role'] === 'admin') {
        $_SESSION['utilisateur'] = [
            'id' => $decoded['id'],
            'nom' => $decoded['nom'],
            'email' => $decoded['email'],
            'role' => $decoded['role'],
            'validation_identite' => $decoded['validation_identite'] ?? null
        ];
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Token JWT invalide ou non admin']);
        exit;
    }
}

try {
    $conn = getConnexion();

    // Paramètres de recherche et de pagination
    $search = $_GET['search'] ?? '';
    $statut_filter = $_GET['statut'] ?? '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    // Construction de la requête de base
    $where_conditions = [];
    $params = [];

    if (!empty($search)) {
        $where_conditions[] = "(client.nom LIKE ? OR livreur.nom LIKE ? OR a.titre LIKE ?)";
        $search_param = "%$search%";
        array_push($params, $search_param, $search_param, $search_param);
    }

    if (!empty($statut_filter)) {
        $where_conditions[] = "l.statut = ?";
        $params[] = $statut_filter;
    }

    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

    // Compter le nombre total de résultats pour la pagination
    $stmt_count = $conn->prepare("SELECT COUNT(l.id) 
                                FROM livraisons l
                                LEFT JOIN utilisateurs client ON l.id_client = client.id 
                                LEFT JOIN utilisateurs livreur ON l.id_livreur = livreur.id 
                                LEFT JOIN annonces a ON l.id_annonce = a.id
                                $where_clause");
    $stmt_count->execute($params);
    $total_results = $stmt_count->fetchColumn();
    $total_pages = ceil($total_results / $limit);

    // Récupérer les livraisons pour la page actuelle
    $query = "SELECT l.*, 
              client.nom as client_nom, client.email as client_email,
              livreur.nom as livreur_nom, livreur.email as livreur_email,
              a.titre as annonce_titre
              FROM livraisons l 
              LEFT JOIN utilisateurs client ON l.id_client = client.id 
              LEFT JOIN utilisateurs livreur ON l.id_livreur = livreur.id 
              LEFT JOIN annonces a ON l.id_annonce = a.id
              $where_clause
              ORDER BY l.date_prise_en_charge DESC
              LIMIT $limit OFFSET $offset";
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $livraisons = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les segments pour chaque livraison
    $stmt_segment = $conn->prepare("SELECT s.*, 
                                   u.nom as livreur_nom, 
                                   pd.nom as point_relais_depart_nom,
                                   pa.nom as point_relais_arrivee_nom
                                   FROM segments s 
                                   LEFT JOIN utilisateurs u ON s.id_livreur = u.id
                                   LEFT JOIN points_relais pd ON s.point_relais_depart = pd.id
                                   LEFT JOIN points_relais pa ON s.point_relais_arrivee = pa.id
                                   WHERE s.id_livraison = ?");

    foreach ($livraisons as $key => $livraison) {
        $stmt_segment->execute([$livraison['id']]);
        $livraisons[$key]['segments'] = $stmt_segment->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode([
        'livraisons' => $livraisons,
        'currentPage' => $page,
        'totalPages' => $total_pages
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de base de données: ' . $e->getMessage()]);
}
?> 