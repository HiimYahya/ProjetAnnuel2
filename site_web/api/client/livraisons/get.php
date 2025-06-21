<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../fonctions/db.php';

if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'client') {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

$conn = getConnexion();
$id_client = $_SESSION['utilisateur']['id'];
$id_annonce = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$id_annonce) {
    echo json_encode(['error' => 'ID manquant']);
    exit;
}

// Récupérer l'annonce
$stmt = $conn->prepare('SELECT * FROM annonces WHERE id = ? AND id_client = ?');
$stmt->execute([$id_annonce, $id_client]);
$annonce = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$annonce) {
    echo json_encode(['error' => 'Annonce introuvable']);
    exit;
}

// Récupérer les segments
$stmt2 = $conn->prepare('SELECT s.*, u.nom as livreur FROM segments s LEFT JOIN utilisateurs u ON s.id_livreur = u.id WHERE s.id_annonce = ? ORDER BY s.ordre ASC');
$stmt2->execute([$id_annonce]);
$segments = [];
foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $seg) {
    $segments[] = [
        'depart' => $seg['adresse_depart'],
        'arrivee' => $seg['adresse_arrivee'],
        'livreur' => $seg['livreur'],
        'statut' => $seg['statut']
    ];
}

echo json_encode(['annonce' => $annonce, 'segments' => $segments]); 