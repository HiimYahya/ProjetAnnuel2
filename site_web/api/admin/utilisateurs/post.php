<?php
session_start();
// api/admin/utilisateurs/post.php

header('Content-Type: application/json');

include '../../../fonctions/db.php';
include '../../../fonctions/fonctions.php';

// Authentification de l'administrateur
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Accès non autorisé']);
    exit;
}

// Assurer que la méthode est POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

// Récupérer les données JSON du corps de la requête
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Données invalides']);
    exit;
}

// Validation des données
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
    echo json_encode(['error' => 'Le rôle sélectionné est invalide.']);
    exit;
}

try {
    $conn = getConnexion();

    // Vérification si l'email existe déjà
    $stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        http_response_code(409); // Conflict
        echo json_encode(['error' => 'Cet email est déjà utilisé par un autre utilisateur.']);
        exit;
    }

    // Génération d'un mot de passe aléatoire
    $password = bin2hex(random_bytes(4)); // 8 caractères
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, role, adresse) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nom, $email, $hashed_password, $role, $adresse]);

    http_response_code(201); // Created
    echo json_encode([
        'success' => 'Utilisateur ajouté avec succès.',
        'password' => $password
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
} 