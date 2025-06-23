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

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$id = $data['id'] ?? null;
$montant = filter_var($data['montant'] ?? 0, FILTER_VALIDATE_FLOAT);
$methode = $data['methode'] ?? '';
$statut = $data['statut'] ?? '';
$id_creancier = !empty($data['id_creancier']) ? $data['id_creancier'] : null;
$id_debiteur = !empty($data['id_debiteur']) ? $data['id_debiteur'] : null;

// Validation
$errors = [];
if (empty($id)) $errors[] = "L'ID du paiement est manquant.";
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
    $stmt = $conn->prepare("UPDATE paiements SET montant = ?, methode = ?, statut = ?, id_creancier = ?, id_debiteur = ? WHERE id = ?");
    $stmt->execute([$montant, $methode, $statut, $id_creancier, $id_debiteur, $id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => 'Paiement mis à jour avec succès.']);
    } else {
        // Potentiellement, aucune modification n'a été faite, ce qui n'est pas une erreur.
        echo json_encode(['success' => 'Paiement mis à jour. (Aucune modification détectée)']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de la mise à jour du paiement: ' . $e->getMessage()]);
}
?> 