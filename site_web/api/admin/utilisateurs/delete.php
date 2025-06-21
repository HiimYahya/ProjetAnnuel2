<?php
session_start();
// api/admin/utilisateurs/delete.php

header('Content-Type: application/json');

include '../../../fonctions/db.php';
include '../../../fonctions/fonctions.php';

// Authentification de l'administrateur
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Accès non autorisé']);
    exit;
}

// Assurer que la méthode est DELETE
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

// Récupérer l'ID depuis les paramètres de la requête
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID utilisateur manquant ou invalide.']);
    exit;
}

$id = (int)$_GET['id'];

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID utilisateur invalide.']);
    exit;
}

try {
    $conn = getConnexion();

    // Vérification : on ne peut pas supprimer son propre compte
    if ($id == $_SESSION['utilisateur']['id']) {
        http_response_code(403);
        echo json_encode(['error' => 'Vous ne pouvez pas supprimer votre propre compte.']);
        exit;
    }

    // Vérification : on ne peut pas supprimer le dernier administrateur
    $stmt = $conn->prepare("SELECT role FROM utilisateurs WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['role'] === 'admin') {
        $stmt_check = $conn->prepare("SELECT COUNT(*) as count FROM utilisateurs WHERE role = 'admin'");
        $stmt_check->execute();
        $result = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] <= 1) {
            http_response_code(403);
            echo json_encode(['error' => 'Impossible de supprimer le dernier administrateur.']);
            exit;
        }
    }

    // Suppression de l'utilisateur
    $stmt = $conn->prepare("DELETE FROM utilisateurs WHERE id = ?");
    if ($stmt->execute([$id])) {
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => 'Utilisateur supprimé avec succès.']);
        } else {
            http_response_code(404); // Not Found
            echo json_encode(['error' => 'Utilisateur non trouvé.']);
        }
    } else {
         http_response_code(500);
         echo json_encode(['error' => 'Erreur lors de la suppression.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
} 