<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../fonctions/db.php';

$conn = getConnexion();

$stats = [];
$stats['utilisateurs'] = (int) $conn->query("SELECT COUNT(*) as total FROM utilisateurs")->fetch(PDO::FETCH_ASSOC)['total'];
$stats['annonces'] = (int) $conn->query("SELECT COUNT(*) as total FROM annonces")->fetch(PDO::FETCH_ASSOC)['total'];
$stats['livraisons'] = (int) $conn->query("SELECT COUNT(*) as total FROM livraisons")->fetch(PDO::FETCH_ASSOC)['total'];
$stats['paiements'] = (int) $conn->query("SELECT COUNT(*) as total FROM paiements")->fetch(PDO::FETCH_ASSOC)['total'];
$stats['montant_total'] = (float) ($conn->query("SELECT SUM(montant) as total FROM paiements WHERE statut = 'effectué'")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

$stmt = $conn->query("SELECT l.*, c.nom as client_nom, lv.nom as livreur_nom 
                     FROM livraisons l 
                     LEFT JOIN utilisateurs c ON l.id_client = c.id 
                     LEFT JOIN utilisateurs lv ON l.id_livreur = lv.id 
                     ORDER BY date_prise_en_charge DESC LIMIT 5");
$stats['livraisons_recentes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->query("SELECT * FROM utilisateurs ORDER BY date_inscription DESC LIMIT 5");
$stats['nouveaux_utilisateurs'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($stats); 