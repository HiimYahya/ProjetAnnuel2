<?php
session_start();
// api/admin/utilisateurs/list.php

header('Content-Type: application/json');

include '../../../fonctions/db.php';
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
    
    // Récupère les livraisons avec le titre de l'annonce associée pour un affichage plus clair
    $query = "SELECT l.id, a.titre 
              FROM livraisons l 
              LEFT JOIN annonces a ON l.id_annonce = a.id 
              ORDER BY l.id DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $livraisons = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($livraisons);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de base de données: ' . $e->getMessage()]);
} 