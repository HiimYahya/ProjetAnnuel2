<?php
session_start();
// api/admin/annonces/delete.php

header('Content-Type: application/json');

include '../../../fonctions/db.php';

if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Accès non autorisé']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID d\'annonce manquant ou invalide.']);
    exit;
}

try {
    $conn = getConnexion();

    // On pourrait ajouter une vérification pour s'assurer que l'annonce n'est pas liée
    // à une livraison en cours, mais pour l'instant on supprime directement.
    
    $stmt = $conn->prepare("DELETE FROM annonces WHERE id = ?");
    
    if ($stmt->execute([$id])) {
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => 'Annonce supprimée avec succès.']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Annonce non trouvée.']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la suppression de l\'annonce.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    // Gestion des contraintes de clé étrangère
    if ($e->getCode() == '23000') {
        echo json_encode(['error' => 'Impossible de supprimer cette annonce car elle est liée à d\'autres éléments (livraisons, segments, etc.).']);
    } else {
        echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
    }
} 