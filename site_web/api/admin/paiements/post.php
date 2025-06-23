<?php
session_start();
header('Content-Type: application/json');
include '../../../fonctions/db.php';
include '../../../fonctions/fonctions.php';

if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Accès non autorisé']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$montant = filter_var($data['montant'] ?? 0, FILTER_VALIDATE_FLOAT);
$methode = $data['methode'] ?? '';
$statut = $data['statut'] ?? '';
$id_creancier = !empty($data['id_creancier']) ? $data['id_creancier'] : null;
$id_debiteur = !empty($data['id_debiteur']) ? $data['id_debiteur'] : null;

// Validation
$errors = [];
if ($montant <= 0) $errors[] = "Le montant doit être un nombre positif.";
if (empty($methode)) $errors[] = "La méthode de paiement est obligatoire.";
if (empty($statut)) $errors[] = "Le statut est obligatoire.";
if (empty($id_creancier)) $errors[] = "Le créancier est obligatoire.";
if (empty($id_debiteur)) $errors[] = "Le débiteur est obligatoire.";


if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['error' => implode(' ', $errors)]);
    exit;
}

try {
    $conn = getConnexion();
    $stmt = $conn->prepare("INSERT INTO paiements (montant, methode, statut, id_creancier, id_debiteur) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$montant, $methode, $statut, $id_creancier, $id_debiteur]);
    
    echo json_encode(['success' => 'Paiement ajouté avec succès.', 'id' => $conn->lastInsertId()]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de l\'ajout du paiement: ' . $e->getMessage()]);
}
?> 