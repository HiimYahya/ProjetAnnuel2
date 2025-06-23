<?php
session_start();
// api/admin/utilisateurs/put.php

header('Content-Type: application/json');

include '../../../fonctions/db.php';
include '../../../fonctions/fonctions.php';
include '../../../fonctions/jwt_utils.php';

// Authentification de l'administrateur
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'admin') {
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
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Accès non autorisé']);
        exit;
    }
}

// Assurer que la méthode est PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Données invalides ou ID manquant.']);
    exit;
}

$id = (int)$data['id'];
$nom = htmlspecialchars($data['nom'] ?? '');
$email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
$role = $data['role'] ?? '';
$adresse = htmlspecialchars($data['adresse'] ?? '');

if (!$nom || !$email || !$role) {
    http_response_code(400);
    echo json_encode(['error' => 'Veuillez remplir tous les champs obligatoires.']);
    exit;
}

if (!in_array($role, ['client', 'livreur', 'commercant', 'prestataire', 'admin'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Rôle invalide.']);
    exit;
}

try {
    $conn = getConnexion();

    // Vérification si le nouvel email est déjà utilisé par un autre utilisateur
    $stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE email = ? AND id != ?");
    $stmt->execute([$email, $id]);
    if ($stmt->fetch()) {
        http_response_code(409); // Conflict
        echo json_encode(['error' => 'Cet email est déjà utilisé par un autre utilisateur.']);
        exit;
    }

    // Mise à jour de l'utilisateur
    $stmt = $conn->prepare("UPDATE utilisateurs SET nom = ?, email = ?, role = ?, adresse = ? WHERE id = ?");
    $stmt->execute([$nom, $email, $role, $adresse, $id]);

    echo json_encode(['success' => 'Utilisateur mis à jour avec succès.']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
} 