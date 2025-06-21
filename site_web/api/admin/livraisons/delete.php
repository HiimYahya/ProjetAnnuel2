<?php
session_start();
header('Content-Type: application/json');
include '../../../fonctions/db.php';
include '../../../fonctions/fonctions.php';

// Vérification de l'authentification et du rôle d'administrateur
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Accès non autorisé']);
    exit;
}

// Accepter uniquement les requêtes DELETE
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$id = $_GET['id'] ?? null;

if (empty($id)) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de la livraison manquant.']);
    exit;
}

$conn = getConnexion();
$conn->beginTransaction();

try {
    // Supprimer les segments liés
    $stmt_segments = $conn->prepare("DELETE FROM segments WHERE id_livraison = ?");
    $stmt_segments->execute([$id]);

    // Supprimer la livraison
    $stmt_livraison = $conn->prepare("DELETE FROM livraisons WHERE id = ?");
    $stmt_livraison->execute([$id]);

    if ($stmt_livraison->rowCount() > 0) {
        $conn->commit();
        echo json_encode(['success' => 'Livraison et ses segments supprimés avec succès.']);
    } else {
        $conn->rollBack();
        http_response_code(404);
        echo json_encode(['error' => 'Livraison non trouvée.']);
    }

} catch (PDOException $e) {
    $conn->rollBack();
    http_response_code(500);
    // En développement, logguer $e->getMessage()
    echo json_encode(['error' => 'Erreur interne du serveur lors de la suppression.']);
}
?> 