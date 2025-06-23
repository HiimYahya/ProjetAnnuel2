<?php
session_start();
header('Content-Type: application/json');
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
    if ($decoded && isset($decoded['role']) && in_array($decoded['role'], ['client', 'admin'])) {
        $_SESSION['utilisateur'] = [
            'id' => $decoded['id'],
            'nom' => $decoded['nom'],
            'email' => $decoded['email'],
            'role' => $decoded['role'],
            'validation_identite' => $decoded['validation_identite'] ?? null
        ];
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Token JWT invalide ou non autorisé']);
        exit;
    }
}

require_once '../../../fonctions/db.php';
$conn = getConnexion();
$id_client = $_SESSION['utilisateur']['id'];
$stmt = $conn->prepare('SELECT description, statut FROM prestations WHERE id = ?');
$stmt->execute([$id_client]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)); 