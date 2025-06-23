<?php
session_start();
// api/admin/annonces/get.php

header('Content-Type: application/json');

include '../../../fonctions/db.php';
include '../../../fonctions/fonctions.php';

// Authentification de l'administrateur
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Accès non autorisé']);
    exit;
}

try {
    $conn = getConnexion();

    // Pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    // Recherche et filtrage
    $search = $_GET['search'] ?? '';
    $statut_filter = $_GET['statut'] ?? '';

    $where_conditions = [];
    $params = [];

    if (!empty($search)) {
        $where_conditions[] = "(a.titre LIKE ? OR a.ville_depart LIKE ? OR a.ville_arrivee LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }

    if (!empty($statut_filter)) {
        $where_conditions[] = "a.statut = ?";
        $params[] = $statut_filter;
    }

    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

    // Comptage total pour pagination
    $count_sql = "SELECT COUNT(a.id) as total FROM annonces a $where_clause";
    $stmt = $conn->prepare($count_sql);
    $stmt->execute($params);
    $total_annonces = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_annonces / $limit);

    // Récupération des annonces avec le nom de l'auteur
    $sql = "SELECT a.*, u.nom as auteur_nom 
            FROM annonces a
            LEFT JOIN utilisateurs u ON a.id_client = u.id
            $where_clause
            ORDER BY a.date_annonce DESC
            LIMIT :limit OFFSET :offset";
    
    $stmt = $conn->prepare($sql);
    
    // Bind les paramètres de la clause WHERE
    $param_index = 1;
    foreach ($params as $value) {
        $stmt->bindValue($param_index++, $value);
    }
    
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $annonces = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'annonces' => $annonces,
        'totalPages' => $total_pages,
        'currentPage' => $page
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de base de données: ' . $e->getMessage()]);
} 