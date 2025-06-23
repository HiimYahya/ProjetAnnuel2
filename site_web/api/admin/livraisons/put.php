<?php
session_start();
header('Content-Type: application/json');
include '../../../fonctions/db.php';
include '../../../fonctions/fonctions.php';
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

// Vérification de l'authentification et du rôle d'administrateur
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Accès non autorisé']);
    exit;
}

// Accepter uniquement les requêtes PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Validation des données
$id = $data['id'] ?? null;
$statut = $data['statut'] ?? '';
$id_livreur = !empty($data['id_livreur']) ? $data['id_livreur'] : null;
$validation_client = !empty($data['validation_client']) ? 1 : 0;
$reception_confirmee = !empty($data['reception_confirmee']) ? 1 : 0;

if (empty($id) || empty($statut)) {
    http_response_code(400);
    echo json_encode(['error' => 'Données incomplètes. ID et statut sont obligatoires.']);
    exit;
}

try {
    $conn = getConnexion();
    $stmt = $conn->prepare("UPDATE livraisons SET 
        statut = ?, 
        id_livreur = ?, 
        validation_client = ?, 
        reception_confirmee = ? 
        WHERE id = ?");
    
    $success = $stmt->execute([$statut, $id_livreur, $validation_client, $reception_confirmee, $id]);

    if ($success) {
        echo json_encode(['success' => 'Livraison mise à jour avec succès']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la mise à jour de la livraison.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    // En développement, vous pourriez vouloir logguer $e->getMessage()
    echo json_encode(['error' => 'Erreur interne du serveur.']);
}
?> 