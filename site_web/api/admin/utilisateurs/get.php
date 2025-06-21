<?php
session_start();
// api/admin/utilisateurs/get.php

header('Content-Type: application/json');

include '../../../fonctions/db.php';
include '../../../fonctions/fonctions.php';

// Authentification de l'administrateur
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'admin') {
    http_response_code(403); // Forbidden
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
    $role_filter = $_GET['role'] ?? '';

    $where_conditions = [];
    $params = [];

    if (!empty($search)) {
        $where_conditions[] = "(nom LIKE ? OR email LIKE ? OR adresse LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }

    if (!empty($role_filter)) {
        $where_conditions[] = "role = ?";
        $params[] = $role_filter;
    }

    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

    // Comptage total pour pagination
    $count_sql = "SELECT COUNT(*) as total FROM utilisateurs $where_clause";
    $stmt = $conn->prepare($count_sql);
    $stmt->execute($params);
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_users / $limit);

    // Récupération des utilisateurs
    $sql = "SELECT id, nom, email, role, date_inscription, adresse, photo_profil FROM utilisateurs $where_clause ORDER BY date_inscription DESC LIMIT :limit OFFSET :offset";
    $stmt = $conn->prepare($sql);
    
    // Bind les paramètres de la clause WHERE
    foreach ($params as $key => $value) {
        $stmt->bindValue($key + 1, $value);
    }
    
    // Bind les paramètres de LIMIT et OFFSET
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'users' => $users,
        'totalPages' => $total_pages,
        'currentPage' => $page
    ]);

} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Erreur de base de données: ' . $e->getMessage()]);
} 