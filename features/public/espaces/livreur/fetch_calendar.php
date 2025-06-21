<?php
session_start();
require_once '../../../../fonctions/db.php';
$conn = getConnexion();

header('Content-Type: application/json');

// Vérifie que le livreur est connecté
if (!isset($_SESSION['utilisateur']['id']) || $_SESSION['utilisateur']['role'] !== 'livreur') {
    echo json_encode([]);
    exit;
}

$id_livreur = $_SESSION['utilisateur']['id'];

// Récupère les livraisons avec une date de livraison
$stmt = $conn->prepare("SELECT id, id_annonce, date_livraison FROM livraisons WHERE id_livreur = ? AND date_livraison IS NOT NULL");
$stmt->execute([$id_livreur]);
$livraisons = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Transforme les données pour FullCalendar
$events = [];
foreach ($livraisons as $livraison) {
    $events[] = [
        'title' => "Livraison #{$livraison['id_annonce']}",
        'start' => date('Y-m-d\TH:i:s', strtotime($livraison['date_livraison'])),
        'url' => 'livraisons.php?id=' . $livraison['id_annonce']
    ];
}

echo json_encode($events);
?>
