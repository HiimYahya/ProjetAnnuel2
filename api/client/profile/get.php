<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['role'] !== 'client') {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}
require_once '../../../fonctions/db.php';
$conn = getConnexion();
$id = $_SESSION['utilisateur']['id'];
$stmt = $conn->prepare('SELECT nom, email, adresse, date_inscription FROM utilisateurs WHERE id = ?');
$stmt->execute([$id]);
echo json_encode($stmt->fetch(PDO::FETCH_ASSOC)); 