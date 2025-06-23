<?php
session_start();
// api/admin/utilisateurs/list.php

header('Content-Type: application/json');

include '../../../fonctions/db.php';

// Authentification de l'administrateur
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Accès non autorisé']);
    exit;
}

try {
    $conn = getConnexion();
    
    $role_filter = $_GET['role'] ?? null;
    $params = [];
    $query = "SELECT id, nom, email FROM utilisateurs";

    if ($role_filter) {
        $query .= " WHERE role = ?";
        $params[] = $role_filter;
    }

    $query .= " ORDER BY nom";

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($users);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de base de données: ' . $e->getMessage()]);
} 