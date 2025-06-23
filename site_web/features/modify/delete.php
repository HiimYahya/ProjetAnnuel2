<?php
include '../../fonctions/db.php';

$conn = getConnexion();

$table = $_GET['table'] ?? '';
$id = $_GET['id'] ?? 0;

if (!$table || !$id) {
    echo "<p>Paramètres manquants.</p>";
    exit;
}

$table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);

$stmt = $conn->prepare("DELETE FROM $table WHERE id = :id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();

header("Location: ../backend.php");
exit;